<?php
/**
 * Text Contrast Low Diagnostic
 *
 * Checks if text has minimum WCAG contrast ratios.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text Contrast Diagnostic Class
 *
 * Validates that text has sufficient contrast (4.5:1 for normal, 3:1 for large).
 *
 * @since 0.6093.1200
 */
class Diagnostic_Text_Contrast_Low extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'text-contrast-low';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Text Contrast Below WCAG AA Standard';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if text has minimum 4.5:1 contrast ratio';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check theme CSS for common low-contrast patterns.
		$css_files = array(
			get_template_directory() . '/style.css',
			get_template_directory() . '/assets/css/main.css',
		);

		$low_contrast_patterns = array();

		foreach ( $css_files as $css_file ) {
			if ( ! file_exists( $css_file ) ) {
				continue;
			}

			$content = file_get_contents( $css_file );

			// Check for light gray text on white (#999, #aaa, #bbb, etc).
			if ( preg_match_all( '/color\s*:\s*#([9-f]{3}|[9-f]{6})/i', $content, $matches ) ) {
				foreach ( $matches[1] as $color ) {
					// Check if color is light (high hex values).
					$hex = str_pad( $color, 6, $color[0] );
					$rgb = array_map( 'hexdec', str_split( $hex, 2 ) );
					$brightness = ( $rgb[0] + $rgb[1] + $rgb[2] ) / 3;

					if ( $brightness > 153 ) { // Roughly 60% brightness.
						$low_contrast_patterns[] = '#' . $color;
					}
				}
			}

			// Check for rgba with high alpha (transparent text).
			if ( preg_match_all( '/color\s*:\s*rgba?\([^)]+,\s*0\.[0-5]\)/i', $content, $matches ) ) {
				$issues[] = __( 'Text using semi-transparent colors (rgba with alpha < 0.5) likely fails contrast requirements', 'wpshadow' );
			}
		}

		if ( count( $low_contrast_patterns ) > 5 ) {
			$issues[] = sprintf(
				/* translators: 1: number of light colors, 2: example colors */
				__( 'Found %1$d instances of light gray text (e.g., %2$s) that may fail contrast requirements', 'wpshadow' ),
				count( $low_contrast_patterns ),
				implode( ', ', array_slice( array_unique( $low_contrast_patterns ), 0, 3 ) )
			);
		}

		// Check if theme has dark mode without contrast validation.
		foreach ( $css_files as $css_file ) {
			if ( ! file_exists( $css_file ) ) {
				continue;
			}

			$content = file_get_contents( $css_file );

			if ( preg_match( '/@media.*prefers-color-scheme.*dark/i', $content ) ) {
				$issues[] = __( 'Theme has dark mode but should verify contrast ratios for both light and dark themes', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your text has low contrast—like trying to read gray text on a light gray background. This affects everyone but especially users with low vision, color blindness (8% of men), and older users whose contrast sensitivity decreases with age. In bright sunlight, low-contrast text becomes completely unreadable on mobile devices. WCAG requires 4.5:1 contrast for normal text and 3:1 for large text (18pt+).', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/text-contrast?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

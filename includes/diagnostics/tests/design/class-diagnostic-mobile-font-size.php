<?php
/**
 * Mobile Font Size - Body Text
 *
 * Validates minimum font size for body text on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Typography
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Font Size - Body Text
 *
 * Ensures body text on mobile is at least 16px to prevent iOS auto-zoom on form focus
 * and to maintain readability without pinch-zoom.
 *
 * @since 1.602.1430
 */
class Diagnostic_Mobile_Font_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-font-size-too-small';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Font Size - Body Text';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures body text is at least 16px on mobile devices';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'typography';

	/**
	 * Minimum font size for body text (prevents iOS auto-zoom).
	 *
	 * @var int
	 */
	const MIN_FONT_SIZE = 16;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$small_text = self::find_small_text();

		if ( empty( $small_text ) ) {
			return null; // No issues found
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %s: current font size */
				__( 'Body text is %s on mobile (minimum: 16px)', 'wpshadow' ),
				$small_text['current_size']
			),
			'severity'        => 'high',
			'threat_level'    => 60,
			'current_size'    => $small_text['current_size'],
			'recommended_size' => '16px minimum',
			'small_text_locations' => $small_text['locations'],
			'user_impact'     => __( 'Forces pinch-zoom to read, iOS auto-zooms on form focus (disrupts UX)', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/mobile-font-size',
		);
	}

	/**
	 * Find body text smaller than minimum.
	 *
	 * @since  1.602.1430
	 * @return array Small text locations.
	 */
	private static function find_small_text(): array {
		// Check theme CSS
		$css = self::get_stylesheet_content();
		if ( ! $css ) {
			return array();
		}

		$locations = array();
		$min_size = self::MIN_FONT_SIZE;

		// Look for body font-size rules
		$patterns = array(
			'body\s*\{[^}]*font-size\s*:\s*(\d+)px' => 'body',
			'body\s*\{[^}]*font-size\s*:\s*([\d.]+)em' => 'body',
			'\.content\s*p\s*\{[^}]*font-size\s*:\s*(\d+)px' => '.content p',
			'p\s*\{[^}]*font-size\s*:\s*(\d+)px' => 'p',
		);

		$smallest_size = PHP_INT_MAX;

		foreach ( $patterns as $pattern => $selector ) {
			if ( preg_match_all( "/$pattern/i", $css, $matches ) ) {
				foreach ( $matches[1] as $size_str ) {
					$size = (int) $size_str;
					if ( $size < $min_size && $size > 0 ) {
						$locations[] = array(
							'selector' => $selector,
							'size'     => $size . 'px',
						);
						$smallest_size = min( $smallest_size, $size );
					}
				}
			}
		}

		if ( empty( $locations ) ) {
			return array();
		}

		return array(
			'current_size' => $smallest_size . 'px',
			'locations'    => array_slice( $locations, 0, 5 ),
		);
	}

	/**
	 * Get theme stylesheet content.
	 *
	 * @since  1.602.1430
	 * @return string|null CSS content.
	 */
	private static function get_stylesheet_content(): ?string {
		$stylesheet = get_template_directory() . '/style.css';

		if ( file_exists( $stylesheet ) ) {
			return file_get_contents( $stylesheet );
		}

		return null;
	}
}

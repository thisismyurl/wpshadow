<?php
/**
 * Font Loading Strategy Diagnostic
 *
 * Analyzes web font loading implementation and performance impact.
 *
 * @since   1.26033.2110
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font Loading Strategy Diagnostic
 *
 * Evaluates font loading strategy and optimization opportunities.
 *
 * @since 1.26033.2110
 */
class Diagnostic_Font_Loading_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'font-loading-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Font Loading Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Evaluates web font loading approach and fallback strategy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2110
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		// Check for Google Fonts
		$google_fonts_detected = false;
		$font_count            = 0;

		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( isset( $style->src ) && strpos( $style->src, 'fonts.googleapis.com' ) !== false ) {
					$google_fonts_detected = true;
					$font_count++;
				}
			}
		}

		// Check for font-display property in CSS
		$has_font_display = false;

		// Query for font-display in custom CSS
		$custom_css = wp_get_custom_css();
		if ( strpos( $custom_css, 'font-display' ) !== false ) {
			$has_font_display = true;
		}

		// Check for local font files in theme directory
		$theme_dir     = get_template_directory();
		$font_extensions = array( '.woff2', '.woff', '.ttf', '.otf' );
		$local_fonts   = false;

		foreach ( glob( $theme_dir . '/fonts/**/*' ) as $file ) {
			foreach ( $font_extensions as $ext ) {
				if ( substr( $file, -strlen( $ext ) ) === $ext ) {
					$local_fonts = true;
					break;
				}
			}
		}

		// Generate findings if issues detected
		if ( $google_fonts_detected && ! $has_font_display && $font_count > 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multiple Google Fonts loaded without font-display optimization. Implement font-display: swap to prevent FOUT.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/font-loading-strategy',
				'meta'         => array(
					'google_fonts_count'  => $font_count,
					'font_display_optimized' => $has_font_display,
					'local_fonts_detected'   => $local_fonts,
					'recommendation'      => 'Add font-display: swap to @font-face declarations',
					'impact_estimate'     => '50-200ms FCP improvement',
					'estimated_size_kb'   => $font_count * 20,
				),
			);
		}

		if ( $google_fonts_detected && $font_count > 4 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of fonts */
					__( '%d web fonts detected. Consider limiting to 2-3 fonts for better performance.', 'wpshadow' ),
					$font_count
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/font-loading-strategy',
				'meta'         => array(
					'google_fonts_count'  => $font_count,
					'recommendation'      => 'Reduce to 2-3 fonts maximum',
					'impact_estimate'     => '15-30ms request time improvement',
					'estimated_size_kb'   => $font_count * 20,
				),
			);
		}

		return null;
	}
}

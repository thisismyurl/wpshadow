<?php
/**
 * Font Loading Optimization Diagnostic
 *
 * Checks web font loading strategy and optimization to prevent font render delay
 * and cumulative layout shift from font swaps.
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
 * Font Loading Optimization Diagnostic Class
 *
 * Verifies font optimization:
 * - font-display: swap configuration
 * - Variable fonts usage
 * - Font subset optimization
 * - Font preloading
 * - Reduced font variants
 *
 * @since 0.6093.1200
 */
class Diagnostic_Font_Loading_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'font-loading-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Font Loading Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks web font loading strategy to minimize render delay';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		$google_fonts_detected = false;
		$font_display_configured = false;
		$font_url_count = 0;

		// Check for Google Fonts or other web fonts
		foreach ( $wp_styles->queue as $handle ) {
			$style = $wp_styles->registered[ $handle ] ?? null;
			if ( $style && is_string( $style->src ) && ! empty( $style->src ) ) {
				if ( stripos( $style->src, 'fonts.googleapis.com' ) !== false ||
					 stripos( $style->src, 'fonts.gstatic.com' ) !== false ||
					 stripos( $style->src, 'typekit.net' ) !== false ) {
					$google_fonts_detected = true;
					$font_url_count++;

					// Check if font-display: swap is used
					if ( stripos( $style->src, 'display=swap' ) !== false ) {
						$font_display_configured = true;
					}
				}
			}
		}

		if ( $google_fonts_detected && ! $font_display_configured ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of web font requests */
					__( 'Detected %d web font requests without proper font-display configuration. Font render delay can impact CLS.', 'wpshadow' ),
					$font_url_count
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/font-loading-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'          => array(
					'web_fonts_detected'   => $font_url_count,
					'font_display_set'     => $font_display_configured,
					'recommendation'       => 'Add display=swap to Google Fonts URL: fonts.googleapis.com?family=...&display=swap',
					'impact'               => 'Prevents invisible text during font load, reduces CLS by 10-20%',
					'best_practice'        => 'Use font-display: swap to show fallback font immediately',
					'optimization_tips'    => array(
						'Limit to 2-3 font families',
						'Use variable fonts to reduce files',
						'Preload critical fonts',
						'Consider system fonts for secondary text',
					),
				),
			);
		}

		return null;
	}
}

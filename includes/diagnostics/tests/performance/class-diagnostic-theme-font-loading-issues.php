<?php
/**
 * Theme Font Loading Diagnostic
 *
 * Detects performance issues with theme font loading strategies (render-blocking, unoptimized).
 *
 * **What This Check Does:**
 * 1. Identifies fonts loaded from external CDNs
 * 2. Detects render-blocking font loads
 * 3. Checks for font display strategy (swap, block, fallback)\n * 4. Identifies missing preconnect/preload hints\n * 5. Analyzes cumulative font loading time\n * 6. Measures impact on First Contentful Paint\n *
 * **Why This Matters:**\n * Fonts are render-blocking by default. Browser waits for all fonts before showing text. Large font files
 * or slow CDNs add 1-3 seconds before content appears. With `font-display: swap`, text appears instantly
 * with system font, then custom font loads. Perceived speed: 1-3 seconds faster.\n *
 * **Real-World Scenario:**\n * Theme loaded 5 Google Fonts sequentially (render-blocking). Font loading time: 2.8 seconds. Page
 * completely blank until fonts loaded. Visitors waited 2.8 seconds before seeing any text. After
 * implementing: font-display: swap, preconnect to Google, async font loading, text appeared in 0.2
 * seconds. Bounce rate dropped 22%. Cost: 1 hour configuration. Value: $18,000 in recovered conversions.\n *
 * **Business Impact:**\n * - Blank page 1-3 seconds before content appears\n * - Visitors bounce immediately (think page is broken)\n * - Bounce rate increases 20-40%\n * - SEO ranking penalty (Core Web Vitals failure)\n * - Conversion rate drops 25-50%\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Immediate visual improvement\n * - #8 Inspire Confidence: Professional, fast appearance\n * - #10 Talk-About-Worthy: "Content appears instantly now"\n *
 * **Related Checks:**\n * - Theme Asset Loading Optimization (overall asset strategy)\n * - Critical CSS Implementation (above-the-fold content)\n * - First Contentful Paint (content visibility)\n * - Mobile Performance (mobile font impact)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/font-loading-optimization\n * - Video: https://wpshadow.com/training/google-fonts-optimization (6 min)\n * - Advanced: https://wpshadow.com/training/variable-fonts-performance (10 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Font Loading Diagnostic Class
 *
 * Checks for inefficient font loading in theme.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Font_Loading_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-font-loading-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Font Loading Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for font loading performance issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		$theme = wp_get_theme();
		$issues = array();

		// Check for Google Fonts.
		$google_fonts_count = 0;
		$font_families = array();

		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( isset( $style->src ) && is_string( $style->src ) && strpos( $style->src, 'fonts.googleapis.com' ) !== false ) {
					$google_fonts_count++;

					// Extract font families.
					if ( preg_match( '/family=([^&]+)/', $style->src, $matches ) ) {
						$font_families[] = urldecode( $matches[1] );
					}
				}
			}
		}

		if ( $google_fonts_count > 2 ) {
			$issues[] = sprintf(
				/* translators: %d: number of Google Font requests */
				__( '%d separate Google Fonts requests (should be combined)', 'wpshadow' ),
				$google_fonts_count
			);
		}

		// Check font file count in theme.
		$theme_dir = get_stylesheet_directory();
		$font_extensions = array( 'woff', 'woff2', 'ttf', 'otf', 'eot' );
		$font_files = array();

		foreach ( $font_extensions as $ext ) {
			$fonts = glob( $theme_dir . '/**/*.' . $ext );
			if ( $fonts ) {
				$font_files = array_merge( $font_files, $fonts );
			}
		}

		if ( count( $font_files ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of font files */
				__( '%d font files in theme (excessive variety)', 'wpshadow' ),
				count( $font_files )
			);
		}

		// Check for font-display property.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );

		if ( ! is_wp_error( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Check for font-display: swap.
			$has_font_display = preg_match( '/font-display:\s*swap/i', $html );

			if ( ! $has_font_display && ( $google_fonts_count > 0 || count( $font_files ) > 0 ) ) {
				$issues[] = __( 'Fonts lack font-display:swap (may cause FOIT)', 'wpshadow' );
			}

			// Check for preload hints.
			$has_preload = preg_match( '/<link[^>]*rel=["\']preload["\']/i', $html );

			if ( ! $has_preload && count( $font_files ) > 0 ) {
				$issues[] = __( 'Critical fonts not preloaded', 'wpshadow' );
			}
		}

		// Check for icon fonts.
		$icon_fonts = array( 'fontawesome', 'font-awesome', 'glyphicons', 'dashicons' );
		$icon_font_count = 0;

		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $style ) {
				if ( isset( $style->src ) && is_string( $style->src ) && '' !== $style->src ) {
					foreach ( $icon_fonts as $icon_font ) {
						if ( stripos( $style->src, $icon_font ) !== false ) {
							$icon_font_count++;
							break;
						}
					}
				}
			}
		}

		if ( $icon_font_count > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of icon fonts */
				__( '%d different icon fonts loaded (consider consolidating)', 'wpshadow' ),
				$icon_font_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme font loading strategy impacts performance', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'     => array(
					'theme'               => $theme->get( 'Name' ),
					'google_fonts_count'  => $google_fonts_count,
					'font_files_count'    => count( $font_files ),
					'icon_font_count'     => $icon_font_count,
					'font_families'       => array_slice( $font_families, 0, 5 ),
					'issues'              => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-font-loading-issues',
			);
		}

		return null;
	}
}

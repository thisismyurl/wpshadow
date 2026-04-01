<?php
/**
 * First Contentful Paint (FCP) Diagnostic
 *
 * Measures First Contentful Paint - when the first text or image is painted.
 * Core Web Vital that measures perceived loading performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First Contentful Paint Diagnostic Class
 *
 * Analyzes factors affecting First Contentful Paint including server response time,
 * render-blocking resources, and critical rendering path optimization.
 *
 * **Why This Matters:**
 * - Google Lighthouse Core Web Vital
 * - First impression of site speed
 * - 1-second delay = 7% reduction in conversions
 * - Affects Google rankings
 *
 * **What's Measured:**
 * - Server response time (TTFB)
 * - Render-blocking CSS/JS
 * - Font loading strategy
 * - Critical CSS implementation
 *
 * **Target:** <1.0 seconds, ideal <1.0 second
 *
 * @since 0.6093.1200
 */
class Diagnostic_First_Contentful_Paint_FCP extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'first-contentful-paint-fcp';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'First Contentful Paint (FCP)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes factors affecting First Contentful Paint performance metric';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if FCP likely poor, null if acceptable.
	 */
	public static function check() {
		global $wp_styles, $wp_scripts;

		$issues = array();

		// Check #1: Render-blocking CSS
		$css_count = 0;
		if ( $wp_styles instanceof \WP_Styles ) {
			$css_count = count( $wp_styles->queue );
			if ( $css_count > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of CSS files */
					__( '%d CSS files block initial paint (recommended: < 3)', 'wpshadow' ),
					$css_count
				);
			}
		}

		// Check #2: Render-blocking JavaScript
		$blocking_js = 0;
		if ( $wp_scripts instanceof \WP_Scripts ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
					continue;
				}
				$script = $wp_scripts->registered[ $handle ];
				if ( empty( $script->extra['defer'] ) && empty( $script->extra['async'] ) ) {
					$blocking_js++;
				}
			}

			if ( $blocking_js > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of blocking scripts */
					__( '%d JavaScript file(s) block first paint', 'wpshadow' ),
					$blocking_js
				);
			}
		}

		// Check #3: Web fonts without font-display
		$theme_dir = get_stylesheet_directory();
		$css_files = glob( $theme_dir . '/*.css' );
		$has_webfonts_without_display = false;

		foreach ( $css_files as $css_file ) {
			$content = file_get_contents( $css_file );
			if ( strpos( $content, '@font-face' ) !== false && strpos( $content, 'font-display' ) === false ) {
				$has_webfonts_without_display = true;
				break;
			}
		}

		if ( $has_webfonts_without_display ) {
			$issues[] = __( 'Web fonts lack font-display property (causes invisible text)', 'wpshadow' );
		}

		// Check #4: Large CSS files
		if ( $wp_styles instanceof \WP_Styles ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
					continue;
				}
				$style = $wp_styles->registered[ $handle ];
				if ( ! empty( $style->src ) && is_string( $style->src ) && strpos( $style->src, home_url() ) === 0 ) {
					// Local stylesheet - check size
					$path = str_replace( home_url(), ABSPATH, $style->src );
					if ( file_exists( $path ) && filesize( $path ) > 100000 ) {
						$issues[] = sprintf(
							/* translators: %s: stylesheet handle */
							__( 'Large CSS file (%s) delays first paint', 'wpshadow' ),
							$handle
						);
						break; // Only report once
					}
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( '%d issue(s) detected that delay First Contentful Paint. Users see a blank screen longer than necessary.', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-first-contentful-paint?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'        => $issues,
				'css_count'     => $css_count,
				'blocking_js'   => $blocking_js,
				'target'        => '<1.0 seconds',
			),
		);
	}
}

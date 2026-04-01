<?php
/**
 * Core Web Vitals Score Diagnostic
 *
 * Checks if homepage meets Core Web Vitals metrics (LCP, FID, CLS).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Web Vitals Score Diagnostic Class
 *
 * Verifies that the site meets Google Core Web Vitals metrics:
 * - LCP (Largest Contentful Paint): < 2.5s
 * - FID (First Input Delay): < 100ms
 * - CLS (Cumulative Layout Shift): < 0.1
 *
 * @since 0.6093.1200
 */
class Diagnostic_Core_Web_Vitals_Score extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-web-vitals-score';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals Score';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if homepage meets Core Web Vitals metrics (LCP, FID, CLS)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the Core Web Vitals diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if metrics issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for Core Web Vitals monitoring plugins.
		$cwv_plugins = array(
			'web-vitals/web-vitals.php',
			'gt-metrix/gt-metrix.php',
		);

		$has_cwv_monitoring = false;
		foreach ( $cwv_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cwv_monitoring = true;
				break;
			}
		}

		$stats['cwv_monitoring'] = $has_cwv_monitoring;

		// Check for Web Vitals data in options (from monitoring plugins).
		$cwv_data = get_option( 'web_vitals_scores' );

		if ( ! empty( $cwv_data ) && is_array( $cwv_data ) ) {
			// Data available from monitoring plugin.
			$stats['has_historical_data'] = true;

			// Check LCP (Largest Contentful Paint).
			if ( isset( $cwv_data['lcp'] ) ) {
				$lcp = floatval( $cwv_data['lcp'] );
				$stats['lcp_score'] = $lcp;

				if ( $lcp > 4 ) {
					$issues[] = sprintf(
						/* translators: %s: LCP in seconds */
						__( 'LCP (Largest Contentful Paint) critically slow: %s seconds (target: <2.5s)', 'wpshadow' ),
						round( $lcp, 2 )
					);
				} elseif ( $lcp > 2.5 ) {
					$warnings[] = sprintf(
						/* translators: %s: LCP in seconds */
						__( 'LCP (Largest Contentful Paint) exceeds target: %s seconds (target: <2.5s)', 'wpshadow' ),
						round( $lcp, 2 )
					);
				}
			}

			// Check FID (First Input Delay).
			if ( isset( $cwv_data['fid'] ) ) {
				$fid = floatval( $cwv_data['fid'] );
				$stats['fid_score'] = $fid;

				if ( $fid > 300 ) {
					$issues[] = sprintf(
						/* translators: %s: FID in ms */
						__( 'FID (First Input Delay) critically slow: %s ms (target: <100ms)', 'wpshadow' ),
						round( $fid, 0 )
					);
				} elseif ( $fid > 100 ) {
					$warnings[] = sprintf(
						/* translators: %s: FID in ms */
						__( 'FID (First Input Delay) exceeds target: %s ms (target: <100ms)', 'wpshadow' ),
						round( $fid, 0 )
					);
				}
			}

			// Check CLS (Cumulative Layout Shift).
			if ( isset( $cwv_data['cls'] ) ) {
				$cls = floatval( $cwv_data['cls'] );
				$stats['cls_score'] = $cls;

				if ( $cls > 0.25 ) {
					$issues[] = sprintf(
						/* translators: %s: CLS score */
						__( 'CLS (Cumulative Layout Shift) too high: %s (target: <0.1)', 'wpshadow' ),
						round( $cls, 3 )
					);
				} elseif ( $cls > 0.1 ) {
					$warnings[] = sprintf(
						/* translators: %s: CLS score */
						__( 'CLS (Cumulative Layout Shift) exceeds target: %s (target: <0.1)', 'wpshadow' ),
						round( $cls, 3 )
					);
				}
			}
		} else {
			$warnings[] = __( 'No Core Web Vitals data available - install monitoring plugin to track metrics', 'wpshadow' );
		}

		// Check for layout shift causes.
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();

		// Check for late-loaded fonts.
		if ( file_exists( $theme_dir . '/style.css' ) ) {
			$css_content = file_get_contents( $theme_dir . '/style.css' );

			if ( preg_match( '/@import.*fonts\.googleapis/', $css_content ) ) {
				$warnings[] = __( 'Google Fonts imported via @import - use preconnect for better performance', 'wpshadow' );
			}
		}

		// Check for render-blocking resources.
		global $wp_styles, $wp_scripts;

		$render_blocking_styles = 0;
		$render_blocking_scripts = 0;

		if ( isset( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( isset( $wp_styles->registered[ $handle ] ) ) {
					$style = $wp_styles->registered[ $handle ];
					// Check if style is critical (render-blocking).
					if ( is_string( $style->src ) && ( strpos( $style->src, 'bootstrap' ) !== false ||
						 strpos( $style->src, 'foundation' ) !== false ) ) {
						$render_blocking_styles++;
					}
				}
			}
		}

		if ( $render_blocking_styles > 2 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d render-blocking stylesheets detected', 'wpshadow' ),
				$render_blocking_styles
			);
		}

		// Check for JavaScript that might cause input delay.
		if ( isset( $wp_scripts->queue ) && count( $wp_scripts->queue ) > 20 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( 'High number of JavaScript files (%d) may cause input delay', 'wpshadow' ),
				count( $wp_scripts->queue )
			);
		}

		// Check for ad networks (known to cause CLS).
		if ( is_plugin_active( 'google-analytics-for-wordpress/googleanalyticsforyourwordpress.php' ) ) {
			$stats['google_analytics'] = true;
		}

		// Check WordPress version (older versions may have less optimization).
		global $wp_version;
		$stats['wordpress_version'] = $wp_version;

		// Check PHP version.
		$stats['php_version'] = phpversion();

		// Check for image optimization.
		$image_optimization_plugins = array(
			'imagify/imagify.php',
			'tinypng/tinypng.php',
			'optimole-wp/optimole-wp.php',
		);

		$has_image_optimization = false;
		foreach ( $image_optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_image_optimization = true;
				break;
			}
		}

		$stats['image_optimization'] = $has_image_optimization;

		if ( ! $has_image_optimization ) {
			$warnings[] = __( 'No image optimization plugin active - consider using one to improve LCP', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Core Web Vitals have critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals-score?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Core Web Vitals have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-web-vitals-score?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Core Web Vitals are good.
	}
}

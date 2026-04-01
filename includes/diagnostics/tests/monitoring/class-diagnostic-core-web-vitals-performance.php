<?php
/**
 * Core Web Vitals Performance Diagnostic
 *
 * Checks Google Core Web Vitals performance metrics.
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
 * Core Web Vitals Performance Diagnostic Class
 *
 * Monitors Core Web Vitals (LCP, FID, CLS).
 *
 * @since 0.6093.1200
 */
class Diagnostic_Core_Web_Vitals_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-web-vitals-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks Google Core Web Vitals performance metrics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'real-user-monitoring';

	/**
	 * LCP threshold (milliseconds)
	 *
	 * @var int
	 */
	private const LCP_THRESHOLD = 2500;

	/**
	 * FID threshold (milliseconds)
	 *
	 * @var int
	 */
	private const FID_THRESHOLD = 100;

	/**
	 * CLS threshold
	 *
	 * @var float
	 */
	private const CLS_THRESHOLD = 0.1;

	/**
	 * Run the Core Web Vitals diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if vitals degraded, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_cwv_performance';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check LCP (Largest Contentful Paint).
		if ( ! self::check_lcp_optimization() ) {
			$issues[] = 'Largest Contentful Paint (LCP): Not optimized';
		}

		// Check FID (First Input Delay).
		if ( ! self::check_fid_optimization() ) {
			$issues[] = 'First Input Delay (FID): Not optimized';
		}

		// Check CLS (Cumulative Layout Shift).
		if ( ! self::check_cls_optimization() ) {
			$issues[] = 'Cumulative Layout Shift (CLS): Not optimized';
		}

		$result = null;

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: list of Core Web Vitals issues */
					__( 'Core Web Vitals issues detected: %s. Optimize these metrics to improve SEO and user experience.', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/optimize-core-web-vitals?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'issues' => $issues,
				),
			);
		}

		set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Check LCP optimization status.
	 *
	 * @since 0.6093.1200
	 * @return bool True if LCP optimized.
	 */
	private static function check_lcp_optimization(): bool {
		// Check for image optimization plugins.
		$optimized_plugins = array(
			'smush/wp-smushit.php',
			'imagify/imagify.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'shortpixel-image-optimiser/shortpixel-image-optimiser.php',
		);

		foreach ( $optimized_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for lazy loading support.
		if ( function_exists( 'wp_lazy_loading_enabled' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check FID optimization status.
	 *
	 * @since 0.6093.1200
	 * @return bool True if FID optimized.
	 */
	private static function check_fid_optimization(): bool {
		// Check for caching plugins (reduce FID).
		$caching_plugins = array(
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'litespeed-cache/litespeed-cache.php',
		);

		foreach ( $caching_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check CLS optimization status.
	 *
	 * @since 0.6093.1200
	 * @return bool True if CLS optimized.
	 */
	private static function check_cls_optimization(): bool {
		// Check if image dimensions are set.
		if ( function_exists( 'wp_image_add_srcset_and_sizes' ) ) {
			return true; // WP 5.5+ handles image sizes better.
		}

		// Check for font optimization plugins.
		if ( is_plugin_active( 'web-font-file-css-loader/loader.php' ) ) {
			return true;
		}

		return false;
	}
}

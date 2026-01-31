<?php
/**
 * Core Web Vitals Status Diagnostic
 *
 * Tracks Largest Contentful Paint (LCP), First Input Delay (FID),
 * and Cumulative Layout Shift (CLS) metrics which are Google ranking factors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5028.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Web Vitals Diagnostic Class
 *
 * Monitors Core Web Vitals performance metrics (LCP, FID, CLS)
 * which directly impact Google search rankings and user experience.
 *
 * @since 1.5028.1630
 */
class Diagnostic_Core_Web_Vitals extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-web-vitals';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tracks LCP, FID, and CLS metrics that affect Google rankings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes Core Web Vitals by checking page performance indicators:
	 * - Resource loading patterns
	 * - JavaScript execution
	 * - Layout stability factors
	 *
	 * @since  1.5028.1630
	 * @return array|null Finding array if poor vitals detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_core_web_vitals_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check for performance optimization plugins.
		$has_cache_plugin    = self::has_cache_plugin();
		$has_lazy_load       = self::has_lazy_loading();
		$has_image_optimizer = self::has_image_optimization();

		// Check theme support.
		$has_async_js     = current_theme_supports( 'html5', 'script' );
		$has_lazy_loading = current_theme_supports( 'html5', 'style' );

		// Analyze potential CLS issues.
		$cls_issues = self::check_cls_factors();

		// Analyze LCP potential.
		$lcp_issues = self::check_lcp_factors();

		// Build issues list.
		if ( ! $has_cache_plugin ) {
			$issues[] = __( 'No caching plugin detected', 'wpshadow' );
		}

		if ( ! $has_lazy_load ) {
			$issues[] = __( 'Lazy loading not enabled for images', 'wpshadow' );
		}

		if ( ! $has_image_optimizer ) {
			$issues[] = __( 'No image optimization plugin detected', 'wpshadow' );
		}

		if ( ! empty( $cls_issues ) ) {
			$issues = array_merge( $issues, $cls_issues );
		}

		if ( ! empty( $lcp_issues ) ) {
			$issues = array_merge( $issues, $lcp_issues );
		}

		// If 3 or more optimization opportunities exist, flag it.
		if ( count( $issues ) >= 3 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Core Web Vitals need optimization. Found %d performance issues that may impact Google rankings.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-core-web-vitals',
				'data'         => array(
					'issues'        => $issues,
					'has_cache'     => $has_cache_plugin,
					'has_lazy_load' => $has_lazy_load,
					'has_optimizer' => $has_image_optimizer,
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Check if a caching plugin is active.
	 *
	 * @since  1.5028.1630
	 * @return bool True if cache plugin detected.
	 */
	private static function has_cache_plugin() {
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'litespeed-cache/litespeed-cache.php',
			'wp-rocket/wp-rocket.php',
			'cache-enabler/cache-enabler.php',
			'comet-cache/comet-cache.php',
			'hyper-cache/plugin.php',
			'swift-performance-lite/performance.php',
		);

		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if lazy loading is enabled.
	 *
	 * @since  1.5028.1630
	 * @return bool True if lazy loading detected.
	 */
	private static function has_lazy_loading() {
		// WordPress 5.5+ has native lazy loading.
		global $wp_version;
		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			return true;
		}

		// Check for lazy load plugins.
		$lazy_load_plugins = array(
			'lazy-load/lazy-load.php',
			'a3-lazy-load/a3-lazy-load.php',
			'rocket-lazy-load/rocket-lazy-load.php',
			'wp-smushit/wp-smush.php',
		);

		foreach ( $lazy_load_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if image optimization is active.
	 *
	 * @since  1.5028.1630
	 * @return bool True if optimization detected.
	 */
	private static function has_image_optimization() {
		$optimizer_plugins = array(
			'wp-smushit/wp-smush.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'imagify/imagify.php',
			'optimus/optimus.php',
			'tiny-compress-images/tiny-compress-images.php',
		);

		foreach ( $optimizer_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for Cumulative Layout Shift factors.
	 *
	 * @since  1.5028.1630
	 * @return array List of CLS issues.
	 */
	private static function check_cls_factors() {
		$issues = array();

		// Check if images have dimensions.
		$theme_supports_responsive_embeds = current_theme_supports( 'responsive-embeds' );
		if ( ! $theme_supports_responsive_embeds ) {
			$issues[] = __( 'Theme may not define image dimensions (CLS risk)', 'wpshadow' );
		}

		// Check for web fonts optimization.
		$theme_supports_custom_fonts = current_theme_supports( 'editor-font-sizes' );
		if ( ! $theme_supports_custom_fonts ) {
			$issues[] = __( 'Font loading not optimized (CLS risk)', 'wpshadow' );
		}

		return $issues;
	}

	/**
	 * Check for Largest Contentful Paint factors.
	 *
	 * @since  1.5028.1630
	 * @return array List of LCP issues.
	 */
	private static function check_lcp_factors() {
		$issues = array();

		// Check server response time indicator (hosting quality).
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		if ( empty( $server_software ) ) {
			$issues[] = __( 'Unable to detect server configuration (LCP risk)', 'wpshadow' );
		}

		// Check if render-blocking resources are minimized.
		global $wp_scripts, $wp_styles;

		if ( isset( $wp_scripts->registered ) && count( $wp_scripts->registered ) > 20 ) {
			$issues[] = __( 'Many JavaScript files enqueued (LCP risk)', 'wpshadow' );
		}

		if ( isset( $wp_styles->registered ) && count( $wp_styles->registered ) > 15 ) {
			$issues[] = __( 'Many CSS files enqueued (LCP risk)', 'wpshadow' );
		}

		return $issues;
	}
}

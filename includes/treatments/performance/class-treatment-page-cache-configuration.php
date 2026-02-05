<?php
/**
 * Page Cache Configuration Treatment
 *
 * Tests if page caching is properly configured for frontend performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1100
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Cache Configuration Treatment Class
 *
 * Validates that page caching is enabled and properly configured
 * for optimal frontend performance.
 *
 * @since 1.7034.1100
 */
class Treatment_Page_Cache_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-cache-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Page Cache Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if page caching is properly configured for frontend performance';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests if page caching is enabled via plugin or server-level
	 * configuration, and validates cache headers are set properly.
	 *
	 * @since  1.7034.1100
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for caching plugins.
		$cache_plugins = array(
			'wp-rocket/wp-rocket.php'                  => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'        => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'              => 'WP Super Cache',
			'wp-fastest-cache/wpFastestCache.php'      => 'WP Fastest Cache',
			'litespeed-cache/litespeed-cache.php'      => 'LiteSpeed Cache',
			'cache-enabler/cache-enabler.php'          => 'Cache Enabler',
			'swift-performance/performance.php'        => 'Swift Performance',
		);

		$active_cache_plugins = array();
		foreach ( $cache_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_cache_plugins[] = $name;
			}
		}

		$has_cache_plugin = ! empty( $active_cache_plugins );

		// Check for advanced-cache.php (server-level caching).
		$has_advanced_cache = file_exists( WP_CONTENT_DIR . '/advanced-cache.php' );

		// Check WP_CACHE constant.
		$wp_cache_enabled = defined( 'WP_CACHE' ) && WP_CACHE;

		// Check cache directory exists.
		$cache_dir = WP_CONTENT_DIR . '/cache';
		$cache_dir_exists = is_dir( $cache_dir );
		$cache_dir_writable = $cache_dir_exists && wp_is_writable( $cache_dir );

		// Check for cache files.
		$cache_file_count = 0;
		if ( $cache_dir_exists && $cache_dir_writable ) {
			$cache_files = glob( $cache_dir . '/*' );
			$cache_file_count = is_array( $cache_files ) ? count( $cache_files ) : 0;
		}

		// Test cache headers on homepage.
		$home_url = home_url( '/' );
		$cache_headers_present = false;
		$cache_control_header = '';

		if ( function_exists( 'wp_remote_get' ) ) {
			$response = wp_remote_get( $home_url, array( 'timeout' => 5 ) );
			if ( ! is_wp_error( $response ) ) {
				$headers = wp_remote_retrieve_headers( $response );
				if ( isset( $headers['cache-control'] ) ) {
					$cache_headers_present = true;
					$cache_control_header = $headers['cache-control'];
				}
			}
		}

		// Check for CDN integration.
		$has_cdn = is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
				  is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
				  is_plugin_active( 'autoptimize/autoptimize.php' );

		// Check for browser caching headers.
		$has_browser_cache = strpos( $cache_control_header, 'max-age=' ) !== false;

		// Check for cache exclusion patterns.
		$has_exclusion_config = false;
		if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$rocket_options = get_option( 'wp_rocket_settings' );
			$has_exclusion_config = ! empty( $rocket_options['cache_reject_uri'] );
		} elseif ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$w3tc_config = get_option( 'w3tc_config' );
			$has_exclusion_config = ! empty( $w3tc_config );
		}

		// Check cache lifetime settings.
		$cache_lifetime = 0;
		if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
			$cache_lifetime = absint( get_option( 'wp_cache_timeout', 3600 ) );
		}

		// Check for mobile cache.
		$has_mobile_cache = is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
						   is_plugin_active( 'w3-total-cache/w3-total-cache.php' );

		// Check for issues.
		$issues = array();

		// Issue 1: No caching plugin or configuration.
		if ( ! $has_cache_plugin && ! $has_advanced_cache ) {
			$issues[] = array(
				'type'        => 'no_cache',
				'description' => __( 'No page caching detected; every page request regenerates HTML', 'wpshadow' ),
			);
		}

		// Issue 2: WP_CACHE not enabled despite cache plugin.
		if ( $has_cache_plugin && ! $wp_cache_enabled ) {
			$issues[] = array(
				'type'        => 'wp_cache_disabled',
				'description' => __( 'Cache plugin installed but WP_CACHE constant not enabled in wp-config.php', 'wpshadow' ),
			);
		}

		// Issue 3: Cache directory not writable.
		if ( $cache_dir_exists && ! $cache_dir_writable ) {
			$issues[] = array(
				'type'        => 'cache_not_writable',
				'description' => __( 'Cache directory exists but is not writable; cached files cannot be created', 'wpshadow' ),
			);
		}

		// Issue 4: No cache files despite plugin active.
		if ( $has_cache_plugin && $cache_file_count === 0 ) {
			$issues[] = array(
				'type'        => 'no_cache_files',
				'description' => __( 'Cache plugin active but no cache files found; caching may not be working', 'wpshadow' ),
			);
		}

		// Issue 5: No cache headers sent.
		if ( ! $cache_headers_present ) {
			$issues[] = array(
				'type'        => 'no_cache_headers',
				'description' => __( 'No cache-control headers sent; browser caching is not configured', 'wpshadow' ),
			);
		}

		// Issue 6: No mobile cache despite mobile traffic.
		if ( $has_cache_plugin && ! $has_mobile_cache ) {
			$issues[] = array(
				'type'        => 'no_mobile_cache',
				'description' => __( 'No separate mobile cache configured; mobile users get desktop cached version', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Page caching is not properly configured, which significantly impacts frontend performance and page load times', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/page-cache-configuration',
				'details'      => array(
					'has_cache_plugin'        => $has_cache_plugin,
					'active_cache_plugins'    => $active_cache_plugins,
					'has_advanced_cache'      => $has_advanced_cache,
					'wp_cache_enabled'        => $wp_cache_enabled,
					'cache_dir_exists'        => $cache_dir_exists,
					'cache_dir_writable'      => $cache_dir_writable,
					'cache_file_count'        => $cache_file_count,
					'cache_headers_present'   => $cache_headers_present,
					'cache_control_header'    => $cache_control_header,
					'has_browser_cache'       => $has_browser_cache,
					'has_cdn'                 => $has_cdn,
					'has_exclusion_config'    => $has_exclusion_config,
					'cache_lifetime'          => $cache_lifetime > 0 ? $cache_lifetime . 's' : 'Not configured',
					'has_mobile_cache'        => $has_mobile_cache,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Install WP Rocket or W3 Total Cache, enable WP_CACHE, set browser cache headers', 'wpshadow' ),
					'performance_improvement' => '70-90% faster page loads with proper caching',
					'cache_types'             => array(
						'Page Cache'    => 'Store generated HTML to avoid PHP execution',
						'Browser Cache' => 'Cache static assets in visitor browsers',
						'Object Cache'  => 'Cache database queries (Redis/Memcached)',
						'CDN Cache'     => 'Serve static files from edge locations',
					),
					'recommended_settings'    => array(
						'cache_lifetime'  => '3600s (1 hour) or longer',
						'mobile_cache'    => 'Separate cache for mobile devices',
						'browser_cache'   => 'max-age=31536000 for static assets',
						'exclusions'      => 'Exclude cart, checkout, account pages',
					),
				),
			);
		}

		return null;
	}
}

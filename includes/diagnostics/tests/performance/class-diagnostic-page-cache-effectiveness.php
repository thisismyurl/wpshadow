<?php
/**
 * Diagnostic: Page Cache Effectiveness
 *
 * Tests page caching plugin effectiveness and coverage.
 * Page caching is #1 performance optimization.
 * Properly cached sites serve pages in 50-100ms vs 1-2 seconds.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1850
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Page_Cache_Effectiveness
 *
 * Tests page caching configuration and effectiveness.
 *
 * @since 1.26028.1850
 */
class Diagnostic_Page_Cache_Effectiveness extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'page-cache-effectiveness';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Page Cache Effectiveness';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests page caching plugin effectiveness and coverage';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check page cache effectiveness.
	 *
	 * @since  1.26028.1850
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Detect page caching plugins.
		$cache_plugin = self::detect_caching_plugin();

		// Estimate traffic level.
		$traffic_level = self::estimate_traffic_level();

		// If no caching on high-traffic site, critical issue.
		if ( ! $cache_plugin && 'high' === $traffic_level ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No page caching detected on high-traffic site. Page caching is the #1 performance optimization - it can reduce page load time from 1-2 seconds to 50-100ms. Install WP Super Cache, W3 Total Cache, or use server-level caching.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/page-cache-effectiveness',
				'meta'         => array(
					'has_caching'      => false,
					'cache_plugin'     => null,
					'traffic_level'    => $traffic_level,
					'recommendation'   => 'Install WP Super Cache or use server caching',
				),
			);
		}

		// If no caching on medium-traffic site, high priority.
		if ( ! $cache_plugin && 'medium' === $traffic_level ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No page caching detected. As your site grows, page caching becomes essential. Install a caching plugin like WP Super Cache to dramatically improve performance.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/page-cache-effectiveness',
				'meta'         => array(
					'has_caching'      => false,
					'cache_plugin'     => null,
					'traffic_level'    => $traffic_level,
					'recommendation'   => 'Install caching plugin for growing traffic',
				),
			);
		}

		// If caching plugin is installed, check if it's working.
		if ( $cache_plugin ) {
			// Check if advanced-cache.php dropin exists (indicates caching active).
			$advanced_cache_exists = file_exists( WP_CONTENT_DIR . '/advanced-cache.php' );

			// Check WP_CACHE constant.
			$wp_cache_enabled = defined( 'WP_CACHE' ) && WP_CACHE;

			// If cache plugin installed but not active.
			if ( ! $advanced_cache_exists && ! $wp_cache_enabled ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: Cache plugin name */
						__( '%s is installed but not active. Enable caching in the plugin settings or add define(\'WP_CACHE\', true); to wp-config.php.', 'wpshadow' ),
						$cache_plugin
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => true,
					'kb_link'      => 'https://wpshadow.com/kb/page-cache-effectiveness',
					'meta'         => array(
						'has_caching'           => true,
						'cache_plugin'          => $cache_plugin,
						'traffic_level'         => $traffic_level,
						'advanced_cache_exists' => $advanced_cache_exists,
						'wp_cache_enabled'      => $wp_cache_enabled,
						'recommendation'        => 'Activate caching in plugin settings',
					),
				);
			}

			// Check for common issues that break caching.
			$cache_breakers = self::detect_cache_breakers();

			if ( ! empty( $cache_breakers ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: List of cache-breaking issues */
						__( 'Page caching is configured but may not be effective. Detected issues: %s. These can prevent pages from being cached.', 'wpshadow' ),
						implode( ', ', $cache_breakers )
					),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/page-cache-effectiveness',
					'meta'         => array(
						'has_caching'      => true,
						'cache_plugin'     => $cache_plugin,
						'traffic_level'    => $traffic_level,
						'cache_breakers'   => $cache_breakers,
						'recommendation'   => 'Fix cache-breaking issues',
					),
				);
			}
		}

		// Page caching is properly configured (or not needed for low traffic).
		return null;
	}

	/**
	 * Detect active caching plugin.
	 *
	 * @since  1.26028.1850
	 * @return string|false Cache plugin name or false if none detected.
	 */
	private static function detect_caching_plugin() {
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php'       => 'WP Super Cache',
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-rocket/wp-rocket.php'           => 'WP Rocket',
			'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
			'cache-enabler/cache-enabler.php'   => 'Cache Enabler',
			'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
			'comet-cache/comet-cache.php'       => 'Comet Cache',
			'hyper-cache/plugin.php'            => 'Hyper Cache',
			'cachify/cachify.php'               => 'Cachify',
			'swift-performance-lite/performance.php' => 'Swift Performance',
			'sg-cachepress/sg-cachepress.php'   => 'SG Optimizer',
			'breeze/breeze.php'                 => 'Breeze (Cloudways)',
		);

		foreach ( $cache_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return $plugin_name;
			}
		}

		// Check for server-level caching.
		if ( function_exists( 'varnish_purge_all' ) ) {
			return 'Varnish (Server-level)';
		}

		if ( defined( 'NGINX_HELPER_BASEPATH' ) || is_plugin_active( 'nginx-helper/nginx-helper.php' ) ) {
			return 'Nginx FastCGI Cache';
		}

		return false;
	}

	/**
	 * Estimate traffic level based on site metrics.
	 *
	 * @since  1.26028.1850
	 * @return string Traffic level: 'low', 'medium', or 'high'.
	 */
	private static function estimate_traffic_level() {
		// Check post count as proxy for site size.
		$post_count = wp_count_posts( 'post' );
		$total_posts = 0;
		if ( $post_count ) {
			foreach ( $post_count as $status => $count ) {
				if ( 'publish' === $status || 'private' === $status ) {
					$total_posts += $count;
				}
			}
		}

		// Check user count.
		$user_count = count_users();
		$total_users = isset( $user_count['total_users'] ) ? $user_count['total_users'] : 0;

		// Check if multisite.
		$is_multisite = is_multisite();

		// Check if e-commerce.
		$is_ecommerce = class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' );

		// Estimate traffic level.
		if ( $total_posts > 10000 || $total_users > 1000 || $is_multisite || $is_ecommerce ) {
			return 'high';
		} elseif ( $total_posts > 1000 || $total_users > 100 ) {
			return 'medium';
		}

		return 'low';
	}

	/**
	 * Detect common issues that break caching.
	 *
	 * @since  1.26028.1850
	 * @return array List of detected cache-breaking issues.
	 */
	private static function detect_cache_breakers() {
		$issues = array();

		// Check for cart/checkout cookies on every page.
		if ( class_exists( 'WooCommerce' ) ) {
			// WooCommerce sets cookies that can break caching.
			if ( ! defined( 'WOOCOMMERCE_CART_CACHE_ENABLED' ) ) {
				$issues[] = __( 'WooCommerce cart cookies on all pages', 'wpshadow' );
			}
		}

		// Check for session_start() calls.
		if ( session_status() === PHP_SESSION_ACTIVE ) {
			$issues[] = __( 'PHP sessions active (prevents caching)', 'wpshadow' );
		}

		// Check for query strings on static resources.
		// This is hard to detect without analyzing actual pages.

		// Check for logged-in users.
		if ( is_user_logged_in() ) {
			// This is expected behavior, but note it.
			// Most cache plugins exclude logged-in users.
		}

		return $issues;
	}
}

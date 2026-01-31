<?php
/**
 * Advanced Cache Drop-In Configuration Diagnostic
 *
 * Validates page cache drop-in (wp-content/advanced-cache.php) if present.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Cache Drop-In Configuration Class
 *
 * Tests whether page cache drop-in is properly configured.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Advanced_Cache_Drop_In_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'advanced-cache-drop-in-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Advanced Cache Drop-In Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates page cache drop-in (wp-content/advanced-cache.php) if present';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$advanced_cache_file = WP_CONTENT_DIR . '/advanced-cache.php';
		$advanced_cache_exists = file_exists( $advanced_cache_file );

		// If advanced-cache.php doesn't exist, no issue.
		if ( ! $advanced_cache_exists ) {
			return null;
		}

		$issues = array();

		// Check if WP_CACHE constant is defined and true.
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'advanced-cache.php exists but WP_CACHE constant is false (cache not enabled)', 'wpshadow' );
		}

		// Check if caching plugin is active.
		$caching_plugin = self::detect_caching_plugin();
		if ( ! $caching_plugin ) {
			$issues[] = __( 'advanced-cache.php exists but no caching plugin detected', 'wpshadow' );
		}

		// Check for potential cache exclusion issues.
		$exclusion_issues = self::check_cache_exclusions();
		if ( ! empty( $exclusion_issues ) ) {
			$issues = array_merge( $issues, $exclusion_issues );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/advanced-cache-drop-in-configuration',
				'meta'         => array(
					'advanced_cache_exists' => true,
					'wp_cache_enabled'      => defined( 'WP_CACHE' ) && WP_CACHE,
					'caching_plugin'        => $caching_plugin,
					'issues_found'          => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Detect which caching plugin is active.
	 *
	 * @since  1.26028.1905
	 * @return string|false Plugin name or false if none detected.
	 */
	private static function detect_caching_plugin() {
		$caching_plugins = array(
			'wp-rocket/wp-rocket.php'                      => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'            => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'                  => 'WP Super Cache',
			'wp-fastest-cache/wpFastestCache.php'          => 'WP Fastest Cache',
			'cache-enabler/cache-enabler.php'              => 'Cache Enabler',
			'litespeed-cache/litespeed-cache.php'          => 'LiteSpeed Cache',
			'comet-cache/comet-cache.php'                  => 'Comet Cache',
			'wp-optimize/wp-optimize.php'                  => 'WP-Optimize',
			'sg-cachepress/sg-cachepress.php'              => 'SiteGround Optimizer',
			'swift-performance-lite/performance.php'       => 'Swift Performance',
			'hyper-cache/plugin.php'                       => 'Hyper Cache',
			'cachify/cachify.php'                          => 'Cachify',
		);

		foreach ( $caching_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return $plugin_name;
			}
		}

		return false;
	}

	/**
	 * Check for cache exclusion issues.
	 *
	 * @since  1.26028.1905
	 * @return array Array of issues found.
	 */
	private static function check_cache_exclusions() {
		$issues = array();

		// Check if user is logged in (we can't fully test this without being logged in).
		// But we can check if common exclusions are likely configured.
		
		// Check for WooCommerce cart/checkout caching.
		if ( class_exists( 'WooCommerce' ) ) {
			// Most caching plugins should exclude cart/checkout.
			// We can't fully test this, but we can warn.
			if ( self::detect_caching_plugin() && ! self::has_woocommerce_exclusions() ) {
				$issues[] = __( 'WooCommerce detected but cart/checkout may not be excluded from cache', 'wpshadow' );
			}
		}

		// Check for membership/restriction plugins.
		$membership_plugins = array(
			'members/members.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'woocommerce-memberships/woocommerce-memberships.php',
			'restrict-content-pro/restrict-content-pro.php',
		);

		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$issues[] = __( 'Membership plugin detected - ensure restricted content is excluded from cache', 'wpshadow' );
				break;
			}
		}

		return $issues;
	}

	/**
	 * Check if WooCommerce exclusions are likely configured.
	 *
	 * @since  1.26028.1905
	 * @return bool True if exclusions are likely configured.
	 */
	private static function has_woocommerce_exclusions() {
		// Check common caching plugin settings for WooCommerce exclusions.
		// This is plugin-specific and approximate.
		
		// W3 Total Cache.
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$w3tc_config = get_option( 'w3tc_pgcache.reject.uri', array() );
			if ( is_array( $w3tc_config ) && ! empty( $w3tc_config ) ) {
				foreach ( $w3tc_config as $uri ) {
					if ( false !== strpos( $uri, 'cart' ) || false !== strpos( $uri, 'checkout' ) ) {
						return true;
					}
				}
			}
		}

		// WP Rocket.
		if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$rocket_options = get_option( 'wp_rocket_settings', array() );
			if ( isset( $rocket_options['cache_reject_uri'] ) && ! empty( $rocket_options['cache_reject_uri'] ) ) {
				if ( false !== strpos( $rocket_options['cache_reject_uri'], 'cart' ) ||
					 false !== strpos( $rocket_options['cache_reject_uri'], 'checkout' ) ) {
					return true;
				}
			}
		}

		// Assume other plugins have auto-detection (many do).
		return true;
	}
}

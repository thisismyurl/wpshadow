<?php
/**
 * Cache Preloading Strategy Effectiveness Diagnostic
 *
 * Tests if cache preloading/warming is configured and effective.
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
 * Cache Preloading Strategy Effectiveness Class
 *
 * Tests whether cache preloading is configured.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Cache_Preloading_Strategy_Effectiveness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-preloading-strategy-effectiveness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Preloading Strategy Effectiveness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if cache preloading/warming is configured and effective';

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
		// First, check if any caching solution is active.
		$caching_plugin = self::detect_caching_plugin();
		if ( ! $caching_plugin ) {
			// No caching plugin, preloading not applicable.
			return null;
		}

		$issues = array();
		$preload_status = self::check_preload_configuration();

		if ( ! $preload_status['preload_enabled'] ) {
			$issues[] = __( 'Cache preloading not enabled (first visitor after cache clear gets slow page)', 'wpshadow' );
		}

		if ( $preload_status['preload_enabled'] && ! $preload_status['uses_sitemap'] ) {
			$issues[] = __( 'Cache preloading not using sitemap (may miss important pages)', 'wpshadow' );
		}

		if ( $preload_status['preload_enabled'] && ! $preload_status['homepage_preloaded'] ) {
			$issues[] = __( 'Homepage not included in cache preloading', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cache-preloading-strategy-effectiveness',
				'meta'         => array(
					'caching_plugin'     => $caching_plugin,
					'preload_enabled'    => $preload_status['preload_enabled'],
					'uses_sitemap'       => $preload_status['uses_sitemap'],
					'homepage_preloaded' => $preload_status['homepage_preloaded'],
					'issues_found'       => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Detect active caching plugin.
	 *
	 * @since  1.26028.1905
	 * @return string|false Plugin name or false.
	 */
	private static function detect_caching_plugin() {
		$caching_plugins = array(
			'wp-rocket/wp-rocket.php'                 => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'       => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'             => 'WP Super Cache',
			'wp-fastest-cache/wpFastestCache.php'     => 'WP Fastest Cache',
			'cache-enabler/cache-enabler.php'         => 'Cache Enabler',
			'litespeed-cache/litespeed-cache.php'     => 'LiteSpeed Cache',
			'comet-cache/comet-cache.php'             => 'Comet Cache',
		);

		foreach ( $caching_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return $plugin_name;
			}
		}

		return false;
	}

	/**
	 * Check preload configuration for active plugin.
	 *
	 * @since  1.26028.1905
	 * @return array Preload status.
	 */
	private static function check_preload_configuration() {
		$status = array(
			'preload_enabled'    => false,
			'uses_sitemap'       => false,
			'homepage_preloaded' => false,
		);

		// WP Rocket.
		if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$rocket_options = get_option( 'wp_rocket_settings', array() );
			
			if ( isset( $rocket_options['manual_preload'] ) && $rocket_options['manual_preload'] ) {
				$status['preload_enabled'] = true;
			}
			
			if ( isset( $rocket_options['sitemap_preload'] ) && $rocket_options['sitemap_preload'] ) {
				$status['uses_sitemap'] = true;
			}
			
			// WP Rocket always preloads homepage.
			$status['homepage_preloaded'] = $status['preload_enabled'];
		}

		// W3 Total Cache.
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$w3tc_config = get_option( 'w3tc_pgcache.prime.enabled', false );
			if ( $w3tc_config ) {
				$status['preload_enabled'] = true;
			}
			
			// Check sitemap configuration.
			$sitemap_config = get_option( 'w3tc_pgcache.prime.sitemap', '' );
			if ( ! empty( $sitemap_config ) ) {
				$status['uses_sitemap'] = true;
			}
			
			$status['homepage_preloaded'] = $status['preload_enabled'];
		}

		// LiteSpeed Cache.
		if ( is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
			$litespeed_config = get_option( 'litespeed.conf.crawler', array() );
			
			if ( isset( $litespeed_config['crawler'] ) && $litespeed_config['crawler'] ) {
				$status['preload_enabled'] = true;
			}
			
			if ( isset( $litespeed_config['crawler_usleep'] ) ) {
				$status['uses_sitemap'] = true; // LiteSpeed uses sitemap by default.
			}
			
			$status['homepage_preloaded'] = $status['preload_enabled'];
		}

		// WP Super Cache.
		if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
			$wpsc_config = get_option( 'wp_super_cache_preload_on', false );
			if ( $wpsc_config ) {
				$status['preload_enabled'] = true;
				$status['homepage_preloaded'] = true;
			}
		}

		// WP Fastest Cache.
		if ( is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ) {
			$wpfc_config = get_option( 'WpFastestCache', array() );
			
			if ( isset( $wpfc_config['wpFastestCachePreload'] ) && 'on' === $wpfc_config['wpFastestCachePreload'] ) {
				$status['preload_enabled'] = true;
				$status['homepage_preloaded'] = true;
			}
		}

		return $status;
	}
}

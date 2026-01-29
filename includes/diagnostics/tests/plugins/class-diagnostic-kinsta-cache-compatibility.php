<?php
/**
 * Kinsta Cache Compatibility Diagnostic
 *
 * Kinsta Cache Compatibility needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.994.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kinsta Cache Compatibility Diagnostic Class
 *
 * @since 1.994.0000
 */
class Diagnostic_KinstaCacheCompatibility extends Diagnostic_Base {

	protected static $slug = 'kinsta-cache-compatibility';
	protected static $title = 'Kinsta Cache Compatibility';
	protected static $description = 'Kinsta Cache Compatibility needs attention';
	protected static $family = 'performance';

	public static function check() {
		// Check if running on Kinsta
		$is_kinsta = defined( 'KINSTAMU_VERSION' ) || isset( $_SERVER['KINSTA_CACHE_ZONE'] );
		if ( ! $is_kinsta ) {
			return null; // Not on Kinsta
		}

		$issues = array();
		$threat_level = 0;

		// Check for conflicting cache plugins
		$conflicting_plugins = array(
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-super-cache/wp-super-cache.php' => 'WP Super Cache',
			'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
			'wp-rocket/wp-rocket.php' => 'WP Rocket',
		);

		foreach ( $conflicting_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$issues[] = 'conflicting_cache_plugin_' . sanitize_key( $plugin_name );
				$threat_level += 20;
			}
		}

		// Check if Kinsta MU plugin is active
		if ( ! defined( 'KINSTAMU_VERSION' ) ) {
			$issues[] = 'kinsta_mu_plugin_missing';
			$threat_level += 15;
		}

		// Check page cache bypass
		$bypass_cookie = isset( $_COOKIE['wordpress_logged_in'] );
		if ( $bypass_cookie && ! defined( 'DONOTCACHEPAGE' ) ) {
			$issues[] = 'logged_in_cache_not_bypassed';
			$threat_level += 10;
		}

		// Check Kinsta API availability
		if ( class_exists( 'Kinsta\Cache' ) ) {
			$api_available = method_exists( 'Kinsta\Cache', 'purge_complete_cache' );
			if ( ! $api_available ) {
				$issues[] = 'kinsta_api_unavailable';
				$threat_level += 10;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of compatibility issues */
				__( 'Kinsta cache compatibility has issues: %s. This can cause cache conflicts, stale content, and degraded performance on Kinsta hosting.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/kinsta-cache-compatibility',
			);
		}
		
		return null;
	}
}

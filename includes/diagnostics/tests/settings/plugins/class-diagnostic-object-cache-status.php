<?php
/**
 * Object Cache Status Diagnostic
 *
 * Checks persistent object cache configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1810
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Object Cache Status Class
 *
 * Validates persistent caching.
 *
 * @since 1.5029.1810
 */
class Diagnostic_Object_Cache_Status extends Diagnostic_Base {

	protected static $slug        = 'object-cache-status';
	protected static $title       = 'Object Cache Status';
	protected static $description = 'Checks persistent object cache';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_object_cache';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wp_object_cache;

		// Check if using default WordPress object cache (no persistence).
		$using_persistent = wp_using_ext_object_cache();

		if ( ! $using_persistent ) {
			// Check database query count.
			global $wpdb;
			$query_count = $wpdb->num_queries;

			if ( $query_count > 50 ) {
				$result = array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: query count */
						__( 'No persistent object cache configured. %d database queries detected. Consider Redis or Memcached.', 'wpshadow' ),
						$query_count
					),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/object-cache-setup',
					'data'         => array(
						'using_persistent_cache' => false,
						'database_queries' => $query_count,
						'cache_type' => 'default',
						'recommendation' => 'Install Redis or Memcached for better performance',
					),
				);

				set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
				return $result;
			}
		} else {
			// Check object cache health.
			$test_key = 'wpshadow_cache_test_' . time();
			$test_value = 'test_' . wp_generate_password( 12, false );
			
			wp_cache_set( $test_key, $test_value, 'wpshadow_test', 60 );
			$retrieved = wp_cache_get( $test_key, 'wpshadow_test' );

			if ( $retrieved !== $test_value ) {
				$result = array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Object cache is configured but not working properly!', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/object-cache-troubleshooting',
					'data'         => array(
						'using_persistent_cache' => true,
						'cache_working' => false,
						'cache_class' => get_class( $wp_object_cache ),
					),
				);

				set_transient( $cache_key, $result, 1 * HOUR_IN_SECONDS );
				return $result;
			}

			wp_cache_delete( $test_key, 'wpshadow_test' );
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}

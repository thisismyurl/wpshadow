<?php
/**
 * W3 Total Cache Object Cache Diagnostic
 *
 * W3 Total Cache Object Cache not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.890.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * W3 Total Cache Object Cache Diagnostic Class
 *
 * @since 1.890.0000
 */
class Diagnostic_W3TotalCacheObjectCache extends Diagnostic_Base {

	protected static $slug = 'w3-total-cache-object-cache';
	protected static $title = 'W3 Total Cache Object Cache';
	protected static $description = 'W3 Total Cache Object Cache not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'W3TC' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Get W3TC configuration
		$config = function_exists( 'w3tc_config' ) ? w3tc_config() : null;
		if ( ! $config ) {
			$issues[] = 'config_unavailable';
			$threat_level += 15;
			return $this->build_finding( $issues, $threat_level );
		}

		// Check if object cache is enabled
		$objectcache_enabled = $config->get_boolean( 'objectcache.enabled' );
		if ( ! $objectcache_enabled ) {
			$issues[] = 'object_cache_disabled';
			$threat_level += 20;
		}

		// Check cache engine
		$engine = $config->get_string( 'objectcache.engine' );
		if ( $engine === 'file' ) {
			$issues[] = 'using_file_cache';
			$threat_level += 15;
		} elseif ( in_array( $engine, array( 'redis', 'memcached' ), true ) ) {
			// Check if Redis/Memcached is actually available
			if ( $engine === 'redis' && ! class_exists( 'Redis' ) ) {
				$issues[] = 'redis_unavailable';
				$threat_level += 20;
			}
			if ( $engine === 'memcached' && ! class_exists( 'Memcached' ) ) {
				$issues[] = 'memcached_unavailable';
				$threat_level += 20;
			}
		}

		// Check cache lifetime
		$lifetime = $config->get_integer( 'objectcache.lifetime' );
		if ( $lifetime < 600 ) {
			$issues[] = 'short_cache_lifetime';
			$threat_level += 10;
		}

		// Check persistent connections
		$persistent = $config->get_boolean( 'objectcache.persistent' );
		if ( ! $persistent && in_array( $engine, array( 'redis', 'memcached' ), true ) ) {
			$issues[] = 'non_persistent_connections';
			$threat_level += 10;
		}

		// Check if cache is working by testing a set/get
		if ( $objectcache_enabled ) {
			$test_key = 'wpshadow_test_' . time();
			wp_cache_set( $test_key, 'test_value', '', 10 );
			$test_result = wp_cache_get( $test_key );
			if ( $test_result !== 'test_value' ) {
				$issues[] = 'cache_not_working';
				$threat_level += 25;
			}
			wp_cache_delete( $test_key );
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of object cache issues */
				__( 'W3 Total Cache object cache has problems: %s. This reduces database performance and slows page generation significantly.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/w3-total-cache-object-cache',
			);
		}
		
		return null;
	}
}

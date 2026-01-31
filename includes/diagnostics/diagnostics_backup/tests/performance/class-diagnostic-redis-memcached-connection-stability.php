<?php
/**
 * Diagnostic: Redis/Memcached Connection Stability Test
 *
 * Tests object cache backend connection reliability and failover.
 * Unstable cache connections cause site-wide errors.
 * Long connection timeouts block page loads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1907
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Redis_Memcached_Connection_Stability
 *
 * Tests cache backend connection stability.
 *
 * @since 1.26028.1907
 */
class Diagnostic_Redis_Memcached_Connection_Stability extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'redis-memcached-connection-stability';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Redis/Memcached Connection Stability Test';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests object cache backend connection reliability and failover';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check cache connection stability.
	 *
	 * @since  1.26028.1907
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_object_cache;

		if ( ! wp_using_ext_object_cache() ) {
			return null;
		}

		$connection_test = self::test_connection_stability();

		if ( ! $connection_test['connected'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cache backend (Redis/Memcached) is not connected. Site is running without object cache, causing performance degradation. Check cache service status and connection configuration.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/redis-memcached-connection-stability',
				'meta'         => array(
					'connected'         => false,
					'cache_type'        => $connection_test['type'],
					'error'             => $connection_test['error'] ?? null,
					'recommendation'    => 'Check cache service and configuration',
				),
			);
		}

		if ( isset( $connection_test['latency'] ) && $connection_test['latency'] > 0.01 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Latency in milliseconds */
					__( 'Cache connection latency is high (%sms). Cache should respond in <10ms. High latency indicates network issues or overloaded cache server.', 'wpshadow' ),
					number_format( $connection_test['latency'] * 1000, 2 )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/redis-memcached-connection-stability',
				'meta'         => array(
					'connected'         => true,
					'cache_type'        => $connection_test['type'],
					'latency_ms'        => $connection_test['latency'] * 1000,
					'recommendation'    => 'Investigate network or cache server performance',
				),
			);
		}

		if ( ! $connection_test['persistent'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cache is using non-persistent connections. Connecting to cache on every request adds overhead. Enable persistent connections in cache configuration.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/redis-memcached-connection-stability',
				'meta'         => array(
					'connected'         => true,
					'cache_type'        => $connection_test['type'],
					'persistent'        => false,
					'recommendation'    => 'Enable persistent connections',
				),
			);
		}

		return null;
	}

	/**
	 * Test cache connection stability.
	 *
	 * @since  1.26028.1907
	 * @return array Connection test results.
	 */
	private static function test_connection_stability() {
		global $wp_object_cache;

		$result = array(
			'connected'  => false,
			'type'       => 'unknown',
			'persistent' => false,
		);

		$test_key = 'wpshadow_conn_test_' . time();
		$test_value = wp_generate_password( 32, false );

		$start_time = microtime( true );
		$set_result = wp_cache_set( $test_key, $test_value, '', 10 );
		$end_time = microtime( true );

		if ( ! $set_result ) {
			$result['error'] = 'Failed to set cache value';
			return $result;
		}

		$result['latency'] = $end_time - $start_time;

		$get_result = wp_cache_get( $test_key );
		if ( $get_result !== $test_value ) {
			$result['error'] = 'Cache value mismatch';
			return $result;
		}

		$result['connected'] = true;

		wp_cache_delete( $test_key );

		if ( method_exists( $wp_object_cache, 'redis_instance' ) ) {
			$result['type'] = 'Redis';
			try {
				$redis = $wp_object_cache->redis_instance();
				if ( $redis instanceof \Redis ) {
					$result['persistent'] = $redis->isConnected();
				}
			} catch ( \Exception $e ) {
				$result['error'] = $e->getMessage();
			}
		} elseif ( method_exists( $wp_object_cache, 'getStats' ) ) {
			$result['type'] = 'Memcached';
			$result['persistent'] = true;
		}

		return $result;
	}
}

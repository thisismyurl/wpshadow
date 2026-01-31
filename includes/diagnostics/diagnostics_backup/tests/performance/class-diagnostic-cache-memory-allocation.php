<?php
/**
 * Diagnostic: Cache Memory Allocation
 *
 * Validates cache systems have adequate memory allocated.
 * Insufficient cache memory causes excessive evictions.
 * Reducing effectiveness to near-zero.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1856
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Cache_Memory_Allocation
 *
 * Tests cache memory allocation and usage.
 *
 * @since 1.26028.1856
 */
class Diagnostic_Cache_Memory_Allocation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cache-memory-allocation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cache Memory Allocation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates cache systems have adequate memory allocated';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check cache memory allocation.
	 *
	 * @since  1.26028.1856
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_object_cache;

		// Check if persistent cache is enabled.
		if ( ! wp_using_ext_object_cache() ) {
			return null; // No persistent cache, so no memory allocation to check.
		}

		// Get cache statistics.
		$cache_stats = self::get_cache_memory_stats();

		if ( ! $cache_stats ) {
			return null; // Can't get stats, assume OK.
		}

		// Check memory exhaustion.
		if ( isset( $cache_stats['memory_used'] ) && isset( $cache_stats['memory_limit'] ) ) {
			$memory_used = $cache_stats['memory_used'];
			$memory_limit = $cache_stats['memory_limit'];
			$memory_percent = ( $memory_limit > 0 ) ? ( $memory_used / $memory_limit ) * 100 : 0;

			// If using >95% of memory, critical issue.
			if ( $memory_percent > 95 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: 1: Memory used percentage, 2: Memory used, 3: Memory limit */
						__( 'Cache memory is %1$s%% full (%2$s of %3$s). Cache is exhausted and evicting entries constantly. Increase cache memory allocation immediately to restore caching effectiveness.', 'wpshadow' ),
						number_format( $memory_percent, 1 ),
						size_format( $memory_used ),
						size_format( $memory_limit )
					),
					'severity'     => 'critical',
					'threat_level' => 80,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/cache-memory-allocation',
					'meta'         => array(
						'memory_used'     => $memory_used,
						'memory_limit'    => $memory_limit,
						'memory_percent'  => $memory_percent,
						'cache_type'      => $cache_stats['type'],
						'recommendation'  => 'Increase cache memory allocation',
					),
				);
			}

			// If using >85% of memory, high priority.
			if ( $memory_percent > 85 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: 1: Memory used percentage, 2: Memory used, 3: Memory limit */
						__( 'Cache memory is %1$s%% full (%2$s of %3$s). Cache will start evicting entries prematurely. Consider increasing cache memory allocation.', 'wpshadow' ),
						number_format( $memory_percent, 1 ),
						size_format( $memory_used ),
						size_format( $memory_limit )
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/cache-memory-allocation',
					'meta'         => array(
						'memory_used'     => $memory_used,
						'memory_limit'    => $memory_limit,
						'memory_percent'  => $memory_percent,
						'cache_type'      => $cache_stats['type'],
						'recommendation'  => 'Plan to increase cache memory',
					),
				);
			}
		}

		// Check eviction rate if available.
		if ( isset( $cache_stats['evictions'] ) && isset( $cache_stats['gets'] ) ) {
			$evictions = $cache_stats['evictions'];
			$gets = $cache_stats['gets'];

			if ( $gets > 0 ) {
				$eviction_rate = ( $evictions / $gets ) * 100;

				// If eviction rate >50%, flag as critical.
				if ( $eviction_rate > 50 ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => sprintf(
							/* translators: 1: Eviction rate percentage, 2: Eviction count */
							__( 'Cache eviction rate is %1$s%% (%2$s evictions). High eviction rates indicate insufficient cache memory. Cache is constantly purging entries before they expire naturally.', 'wpshadow' ),
							number_format( $eviction_rate, 1 ),
							number_format( $evictions )
						),
						'severity'     => 'critical',
						'threat_level' => 75,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/cache-memory-allocation',
						'meta'         => array(
							'eviction_rate'   => $eviction_rate,
							'evictions'       => $evictions,
							'gets'            => $gets,
							'cache_type'      => $cache_stats['type'],
							'recommendation'  => 'Increase cache memory to reduce evictions',
						),
					);
				}

				// If eviction rate >25%, medium priority.
				if ( $eviction_rate > 25 ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => sprintf(
							/* translators: 1: Eviction rate percentage */
							__( 'Cache eviction rate is %1$s%%. Cache is evicting entries before they expire. Consider increasing cache memory allocation for better performance.', 'wpshadow' ),
							number_format( $eviction_rate, 1 )
						),
						'severity'     => 'medium',
						'threat_level' => 60,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/cache-memory-allocation',
						'meta'         => array(
							'eviction_rate'   => $eviction_rate,
							'evictions'       => $evictions,
							'gets'            => $gets,
							'cache_type'      => $cache_stats['type'],
							'recommendation'  => 'Monitor and plan memory increase',
						),
					);
				}
			}
		}

		// Cache memory is adequately allocated.
		return null;
	}

	/**
	 * Get cache memory statistics.
	 *
	 * @since  1.26028.1856
	 * @return array|null Cache stats or null if unavailable.
	 */
	private static function get_cache_memory_stats() {
		global $wp_object_cache;

		$stats = array();

		// Try Redis first.
		if ( method_exists( $wp_object_cache, 'redis_instance' ) ) {
			try {
				$redis = $wp_object_cache->redis_instance();
				if ( $redis instanceof \Redis || $redis instanceof \RedisCluster ) {
					$info = $redis->info();

					if ( isset( $info['used_memory'] ) ) {
						$stats['memory_used'] = (int) $info['used_memory'];
						$stats['type'] = 'Redis';

						if ( isset( $info['maxmemory'] ) ) {
							$stats['memory_limit'] = (int) $info['maxmemory'];
						}

						if ( isset( $info['evicted_keys'] ) ) {
							$stats['evictions'] = (int) $info['evicted_keys'];
						}

						if ( isset( $info['keyspace_hits'] ) && isset( $info['keyspace_misses'] ) ) {
							$stats['gets'] = (int) $info['keyspace_hits'] + (int) $info['keyspace_misses'];
						}

						return $stats;
					}
				}
			} catch ( \Exception $e ) {
				// Redis not available or error.
			}
		}

		// Try Memcached.
		if ( method_exists( $wp_object_cache, 'getStats' ) ) {
			try {
				$memcached_stats = $wp_object_cache->getStats();

				if ( is_array( $memcached_stats ) ) {
					// Get first server's stats.
					$first_server = reset( $memcached_stats );

					if ( isset( $first_server['bytes'] ) ) {
						$stats['memory_used'] = (int) $first_server['bytes'];
						$stats['type'] = 'Memcached';

						if ( isset( $first_server['limit_maxbytes'] ) ) {
							$stats['memory_limit'] = (int) $first_server['limit_maxbytes'];
						}

						if ( isset( $first_server['evictions'] ) ) {
							$stats['evictions'] = (int) $first_server['evictions'];
						}

						if ( isset( $first_server['cmd_get'] ) ) {
							$stats['gets'] = (int) $first_server['cmd_get'];
						}

						return $stats;
					}
				}
			} catch ( \Exception $e ) {
				// Memcached not available or error.
			}
		}

		return null;
	}
}

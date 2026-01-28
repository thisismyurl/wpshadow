<?php
/**
 * Diagnostic: Object Cache Implementation
 *
 * Validates persistent object cache (Redis, Memcached) is properly configured.
 * Without persistent object cache, WordPress queries database repeatedly.
 * Proper caching can reduce database load by 80%+.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1840
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Object_Cache_Implementation
 *
 * Tests persistent object cache configuration and performance.
 *
 * @since 1.26028.1840
 */
class Diagnostic_Object_Cache_Implementation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'object-cache-implementation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Object Cache Implementation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates persistent object cache (Redis, Memcached) is properly configured';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check object cache implementation.
	 *
	 * @since  1.26028.1840
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_object_cache;

		// Check if object-cache.php dropin exists.
		$object_cache_dropin = WP_CONTENT_DIR . '/object-cache.php';
		$has_dropin          = file_exists( $object_cache_dropin );

		// Check if persistent cache is enabled.
		$using_persistent_cache = wp_using_ext_object_cache();

		// If no persistent cache on potentially high-traffic site.
		if ( ! $using_persistent_cache ) {
			$traffic_level = self::estimate_traffic_level();

			// Only warn for medium/high traffic sites.
			if ( 'high' === $traffic_level ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'No persistent object cache detected on high-traffic site. Implementing Redis or Memcached can reduce database queries by 80%+ and dramatically improve performance.', 'wpshadow' ),
					'severity'     => 'critical',
					'threat_level' => 75,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/object-cache-implementation',
					'meta'         => array(
						'has_dropin'      => $has_dropin,
						'persistent'      => false,
						'traffic_level'   => $traffic_level,
						'recommendation'  => 'Install Redis or Memcached object cache',
					),
				);
			} elseif ( 'medium' === $traffic_level ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'No persistent object cache detected. As your site grows, consider implementing Redis or Memcached to reduce database load.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/object-cache-implementation',
					'meta'         => array(
						'has_dropin'      => $has_dropin,
						'persistent'      => false,
						'traffic_level'   => $traffic_level,
						'recommendation'  => 'Consider Redis or Memcached for growing traffic',
					),
				);
			}

			// Low traffic sites don't need persistent cache.
			return null;
		}

		// Check if cache is actually connected.
		if ( ! self::test_cache_connectivity() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Persistent object cache is configured but not connected. Redis or Memcached service may be down or misconfigured.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/object-cache-implementation',
				'meta'         => array(
					'has_dropin'      => $has_dropin,
					'persistent'      => true,
					'connected'       => false,
					'recommendation'  => 'Check Redis/Memcached service status',
				),
			);
		}

		// Check cache hit rate if possible.
		$cache_stats = self::get_cache_statistics();
		if ( $cache_stats && isset( $cache_stats['hit_rate'] ) ) {
			$hit_rate = $cache_stats['hit_rate'];

			// Warn if hit rate is below 50%.
			if ( $hit_rate < 50 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: 1: Cache hit rate percentage */
						__( 'Object cache hit rate is %1$s%% (should be >80%%). Low hit rates indicate cache configuration issues or insufficient cache memory.', 'wpshadow' ),
						number_format( $hit_rate, 1 )
					),
					'severity'     => 'high',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/object-cache-implementation',
					'meta'         => array(
						'has_dropin'      => $has_dropin,
						'persistent'      => true,
						'connected'       => true,
						'hit_rate'        => $hit_rate,
						'recommendation'  => 'Increase cache memory or review cache configuration',
					),
				);
			} elseif ( $hit_rate < 80 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: 1: Cache hit rate percentage */
						__( 'Object cache hit rate is %1$s%% (target: >80%%). Consider optimizing cache configuration or increasing memory allocation.', 'wpshadow' ),
						number_format( $hit_rate, 1 )
					),
					'severity'     => 'medium',
					'threat_level' => 45,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/object-cache-implementation',
					'meta'         => array(
						'has_dropin'      => $has_dropin,
						'persistent'      => true,
						'connected'       => true,
						'hit_rate'        => $hit_rate,
						'recommendation'  => 'Optimize cache configuration for better hit rates',
					),
				);
			}
		}

		// Object cache is properly configured and performing well.
		return null;
	}

	/**
	 * Estimate traffic level based on site metrics.
	 *
	 * @since  1.26028.1840
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

		// Estimate traffic level.
		if ( $total_posts > 10000 || $total_users > 1000 || $is_multisite ) {
			return 'high';
		} elseif ( $total_posts > 1000 || $total_users > 100 ) {
			return 'medium';
		}

		return 'low';
	}

	/**
	 * Test if cache is actually connected and working.
	 *
	 * @since  1.26028.1840
	 * @return bool True if cache is connected, false otherwise.
	 */
	private static function test_cache_connectivity() {
		$test_key   = 'wpshadow_cache_test_' . time();
		$test_value = wp_generate_password( 32, false );

		// Try to set a cache value.
		$set_result = wp_cache_set( $test_key, $test_value, '', 10 );
		if ( ! $set_result ) {
			return false;
		}

		// Try to retrieve the cache value.
		$get_result = wp_cache_get( $test_key );
		if ( $get_result !== $test_value ) {
			return false;
		}

		// Clean up test value.
		wp_cache_delete( $test_key );

		return true;
	}

	/**
	 * Get cache statistics if available.
	 *
	 * @since  1.26028.1840
	 * @return array|null Cache statistics or null if unavailable.
	 */
	private static function get_cache_statistics() {
		global $wp_object_cache;

		// Try to get Redis stats.
		if ( method_exists( $wp_object_cache, 'getStats' ) ) {
			$stats = $wp_object_cache->getStats();
			if ( is_array( $stats ) && ! empty( $stats ) ) {
				return self::parse_redis_stats( $stats );
			}
		}

		// Try to get Memcached stats.
		if ( method_exists( $wp_object_cache, 'stats' ) ) {
			$stats = $wp_object_cache->stats();
			if ( is_array( $stats ) && ! empty( $stats ) ) {
				return self::parse_memcached_stats( $stats );
			}
		}

		// Try alternative methods to get cache info.
		if ( isset( $wp_object_cache->cache_hits ) && isset( $wp_object_cache->cache_misses ) ) {
			$hits   = (int) $wp_object_cache->cache_hits;
			$misses = (int) $wp_object_cache->cache_misses;
			$total  = $hits + $misses;

			if ( $total > 0 ) {
				return array(
					'hit_rate' => ( $hits / $total ) * 100,
					'hits'     => $hits,
					'misses'   => $misses,
				);
			}
		}

		return null;
	}

	/**
	 * Parse Redis statistics.
	 *
	 * @since  1.26028.1840
	 * @param  array $stats Raw Redis stats.
	 * @return array Parsed statistics.
	 */
	private static function parse_redis_stats( $stats ) {
		$result = array();

		if ( isset( $stats['keyspace_hits'] ) && isset( $stats['keyspace_misses'] ) ) {
			$hits   = (int) $stats['keyspace_hits'];
			$misses = (int) $stats['keyspace_misses'];
			$total  = $hits + $misses;

			if ( $total > 0 ) {
				$result['hit_rate'] = ( $hits / $total ) * 100;
				$result['hits']     = $hits;
				$result['misses']   = $misses;
			}
		}

		return $result;
	}

	/**
	 * Parse Memcached statistics.
	 *
	 * @since  1.26028.1840
	 * @param  array $stats Raw Memcached stats.
	 * @return array Parsed statistics.
	 */
	private static function parse_memcached_stats( $stats ) {
		$result = array();

		// Memcached stats are per-server, get first server's stats.
		$first_server = is_array( $stats ) ? reset( $stats ) : array();

		if ( isset( $first_server['get_hits'] ) && isset( $first_server['get_misses'] ) ) {
			$hits   = (int) $first_server['get_hits'];
			$misses = (int) $first_server['get_misses'];
			$total  = $hits + $misses;

			if ( $total > 0 ) {
				$result['hit_rate'] = ( $hits / $total ) * 100;
				$result['hits']     = $hits;
				$result['misses']   = $misses;
			}
		}

		return $result;
	}
}

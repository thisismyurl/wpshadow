<?php
/**
 * Diagnostic: Database Query Caching
 *
 * Validates database query results are being cached appropriately.
 * Query caching reduces database load by 60-80% for repeated queries.
 * Essential for high-traffic sites.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1852
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Database_Query_Caching
 *
 * Tests database query caching configuration and usage.
 *
 * @since 1.26028.1852
 */
class Diagnostic_Database_Query_Caching extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-caching';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Caching';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates database query results are being cached appropriately';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check database query caching.
	 *
	 * @since  1.26028.1852
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Estimate traffic level.
		$traffic_level = self::estimate_traffic_level();

		// Low traffic sites don't need aggressive query caching.
		if ( 'low' === $traffic_level ) {
			return null;
		}

		// Check MySQL query cache status (if available).
		$mysql_query_cache = self::check_mysql_query_cache();

		// Check transient usage for expensive queries.
		$transient_count = self::get_transient_count();

		// Check for persistent object cache.
		$has_object_cache = wp_using_ext_object_cache();

		// If high-traffic site without object cache or query cache.
		if ( 'high' === $traffic_level && ! $has_object_cache && ! $mysql_query_cache['enabled'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'High-traffic site without query caching. MySQL query cache is disabled and no persistent object cache detected. Database queries are hitting MySQL directly on every request. Install Redis/Memcached and enable MySQL query cache.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-caching',
				'meta'         => array(
					'traffic_level'         => $traffic_level,
					'has_object_cache'      => $has_object_cache,
					'mysql_query_cache'     => $mysql_query_cache,
					'transient_count'       => $transient_count,
					'recommendation'        => 'Install Redis/Memcached and enable MySQL query cache',
				),
			);
		}

		// If medium-high traffic without persistent cache.
		if ( 'high' === $traffic_level && ! $has_object_cache ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No persistent object cache on high-traffic site. WordPress is caching in PHP memory which resets on every request. Install Redis or Memcached for persistent query caching.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-caching',
				'meta'         => array(
					'traffic_level'         => $traffic_level,
					'has_object_cache'      => $has_object_cache,
					'mysql_query_cache'     => $mysql_query_cache,
					'transient_count'       => $transient_count,
					'recommendation'        => 'Install persistent object cache',
				),
			);
		}

		// Check for low transient usage on medium-high traffic.
		if ( 'medium' === $traffic_level && $transient_count < 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: Transient count */
					__( 'Only %d transients found. Transients cache expensive query results to reduce database load. Consider using transients for complex queries that don\'t change frequently.', 'wpshadow' ),
					$transient_count
				),
				'severity'     => 'info',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-caching',
				'meta'         => array(
					'traffic_level'         => $traffic_level,
					'has_object_cache'      => $has_object_cache,
					'mysql_query_cache'     => $mysql_query_cache,
					'transient_count'       => $transient_count,
					'recommendation'        => 'Use transients for expensive queries',
				),
			);
		}

		// Check MySQL query cache effectiveness if enabled.
		if ( $mysql_query_cache['enabled'] && isset( $mysql_query_cache['hit_rate'] ) ) {
			$hit_rate = $mysql_query_cache['hit_rate'];

			if ( $hit_rate < 50 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: Hit rate percentage */
						__( 'MySQL query cache hit rate is %s%% (should be >80%%). Low hit rates indicate cache is too small or queries are not cacheable. Increase query_cache_size in MySQL config.', 'wpshadow' ),
						number_format( $hit_rate, 1 )
					),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-query-caching',
					'meta'         => array(
						'traffic_level'         => $traffic_level,
						'has_object_cache'      => $has_object_cache,
						'mysql_query_cache'     => $mysql_query_cache,
						'transient_count'       => $transient_count,
						'recommendation'        => 'Increase MySQL query cache size',
					),
				);
			}
		}

		// Query caching is adequately configured.
		return null;
	}

	/**
	 * Check MySQL query cache status.
	 *
	 * @since  1.26028.1852
	 * @return array Query cache information.
	 */
	private static function check_mysql_query_cache() {
		global $wpdb;

		$result = array(
			'enabled' => false,
			'size'    => 0,
		);

		// Try to get query cache variables.
		$variables = $wpdb->get_results( "SHOW VARIABLES LIKE 'query_cache%'", OBJECT_K );

		if ( empty( $variables ) ) {
			return $result;
		}

		// Check if query cache is enabled.
		if ( isset( $variables['query_cache_type'] ) && 'ON' === $variables['query_cache_type']->Value ) {
			$result['enabled'] = true;
		}

		// Get query cache size.
		if ( isset( $variables['query_cache_size'] ) ) {
			$result['size'] = (int) $variables['query_cache_size']->Value;
		}

		// Get query cache statistics.
		$status = $wpdb->get_results( "SHOW STATUS LIKE 'Qcache%'", OBJECT_K );

		if ( ! empty( $status ) ) {
			if ( isset( $status['Qcache_hits'] ) && isset( $status['Qcache_inserts'] ) ) {
				$hits    = (int) $status['Qcache_hits']->Value;
				$inserts = (int) $status['Qcache_inserts']->Value;
				$total   = $hits + $inserts;

				if ( $total > 0 ) {
					$result['hit_rate'] = ( $hits / $total ) * 100;
					$result['hits']     = $hits;
					$result['inserts']  = $inserts;
				}
			}
		}

		return $result;
	}

	/**
	 * Get count of active transients.
	 *
	 * @since  1.26028.1852
	 * @return int Number of transients.
	 */
	private static function get_transient_count() {
		global $wpdb;

		// Count transients in options table.
		$count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'"
		);

		return $count;
	}

	/**
	 * Estimate traffic level based on site metrics.
	 *
	 * @since  1.26028.1852
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
}

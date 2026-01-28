<?php
/**
 * Query Complexity Index
 *
 * Analyzes the most expensive database queries and suggests optimization
 * opportunities to improve site performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Database
 * @since      1.6028.1051
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Complexity Index Diagnostic Class
 *
 * Identifies expensive database queries and provides optimization suggestions.
 *
 * @since 1.6028.1051
 */
class Diagnostic_Query_Complexity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-complexity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query Complexity Index';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes expensive database queries and suggests optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1051
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_query_complexity_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check if query logging is available.
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$analysis = self::analyze_queries();

		if ( ! $analysis['has_slow_queries'] ) {
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of slow queries */
				__( 'Found %d queries taking over 0.1 seconds, indicating optimization opportunities.', 'wpshadow' ),
				$analysis['slow_query_count']
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/query-optimization',
			'meta'         => array(
				'slow_query_count' => $analysis['slow_query_count'],
				'slowest_query_time' => $analysis['slowest_time'],
				'total_query_time' => $analysis['total_time'],
				'slow_queries' => $analysis['slow_queries'],
			),
			'details'      => array(
				sprintf(
					/* translators: %s: query time in seconds */
					__( 'Slowest query took %s seconds', 'wpshadow' ),
					number_format( $analysis['slowest_time'], 4 )
				),
				__( 'Slow queries impact page load times', 'wpshadow' ),
				__( 'May benefit from database indexing', 'wpshadow' ),
			),
			'recommendation' => __( 'Review slow queries and add database indexes, optimize query structure, or implement caching.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 6 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Analyze queries.
	 *
	 * @since  1.6028.1051
	 * @return array Analysis results.
	 */
	private static function analyze_queries() {
		global $wpdb;

		if ( empty( $wpdb->queries ) ) {
			return array( 'has_slow_queries' => false );
		}

		$slow_queries = array();
		$total_time   = 0;
		$slowest_time = 0;

		foreach ( $wpdb->queries as $query ) {
			list( $sql, $time, $stack ) = $query;

			$time = (float) $time;
			$total_time += $time;
			$slowest_time = max( $slowest_time, $time );

			// Flag queries over 0.1 seconds.
			if ( $time > 0.1 ) {
				$slow_queries[] = array(
					'query' => substr( $sql, 0, 200 ), // Truncate for display.
					'time'  => $time,
				);
			}
		}

		usort( $slow_queries, function( $a, $b ) {
			return $b['time'] <=> $a['time'];
		} );

		return array(
			'has_slow_queries'  => ! empty( $slow_queries ),
			'slow_query_count'  => count( $slow_queries ),
			'slowest_time'      => $slowest_time,
			'total_time'        => round( $total_time, 4 ),
			'slow_queries'      => array_slice( $slow_queries, 0, 5 ),
		);
	}
}

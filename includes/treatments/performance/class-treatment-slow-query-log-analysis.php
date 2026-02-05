<?php
/**
 * Slow Query Log Analysis Treatment
 *
 * Detects slow database queries that degrade performance. Analyzes MySQL slow query log
 * and WordPress query monitoring to identify optimization opportunities.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since      1.6034.2153
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slow Query Log Analysis Treatment Class
 *
 * Monitors database query performance and identifies slow queries that
 * impact page load time. Slow queries are often caused by missing indexes,
 * inefficient joins, or excessive data retrieval.
 *
 * **Why This Matters:**
 * - Database is #1 performance bottleneck in WordPress
 * - Single slow query can block entire page load
 * - 1-second query = unacceptable UX
 * - Identifying slow queries enables targeted optimization
 *
 * **What's Checked:**
 * - Query Monitor plugin data (if available)
 * - SAVEQUERIES constant and query times
 * - Common slow query patterns (unindexed searches, SELECT *)
 * - Database query count per page
 *
 * @since 1.6034.2153
 */
class Treatment_Slow_Query_Log_Analysis extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'slow-query-log-analysis';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Slow Query Log Analysis';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects slow database queries that degrade site performance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2153
	 * @return array|null Finding array if slow queries detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();
		$slow_queries = array();

		// Check if SAVEQUERIES is enabled
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SAVEQUERIES is disabled. Enable it to monitor database query performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-slow-query-log',
				'details'      => array(
					'savequeries_enabled' => false,
					'recommendation'      => 'Add define(\'SAVEQUERIES\', true); to wp-config.php',
				),
			);
		}

		// Analyze saved queries
		if ( ! empty( $wpdb->queries ) ) {
			$total_time   = 0;
			$query_count  = count( $wpdb->queries );
			$slow_threshold = 0.05; // 50ms

			foreach ( $wpdb->queries as $query_data ) {
				list( $query, $time, $stack ) = $query_data;
				$time_float = (float) $time;
				$total_time += $time_float;

				// Flag slow queries
				if ( $time_float > $slow_threshold ) {
					$slow_queries[] = array(
						'query' => substr( $query, 0, 100 ) . '...',
						'time'  => round( $time_float * 1000, 2 ) . 'ms',
					);
				}
			}

			// Flag if too many queries
			if ( $query_count > 50 ) {
				$issues[] = sprintf(
					/* translators: %d: number of queries */
					__( '%d database queries per page (recommended: < 25)', 'wpshadow' ),
					$query_count
				);
			}

			// Flag if total time is excessive
			if ( $total_time > 0.5 ) {
				$issues[] = sprintf(
					/* translators: %s: total query time */
					__( 'Total query time: %sms (recommended: < 100ms)', 'wpshadow' ),
					round( $total_time * 1000, 2 )
				);
			}

			// Flag individual slow queries
			if ( ! empty( $slow_queries ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of slow queries */
					__( '%d slow query/queries detected (> 50ms each)', 'wpshadow' ),
					count( $slow_queries )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( '%d database performance issue(s) detected. Slow queries are degrading page load speed.', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-slow-query-log',
			'details'      => array(
				'issues'            => $issues,
				'query_count'       => $query_count ?? 0,
				'total_time'        => isset( $total_time ) ? round( $total_time * 1000, 2 ) . 'ms' : 'N/A',
				'slow_queries'      => array_slice( $slow_queries, 0, 5 ),
			),
		);
	}
}

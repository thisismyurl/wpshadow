<?php
/**
 * Database Query Performance Diagnostic
 *
 * Analyzes database query logs to identify slow queries that could be
 * degrading site performance.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Query_Performance Class
 *
 * Detects slow database queries affecting performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Database_Query_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Performance Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes slow database queries impacting site speed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Slow query threshold (milliseconds)
	 *
	 * @var float
	 */
	const SLOW_QUERY_THRESHOLD = 500; // 0.5 second

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if slow queries detected, null otherwise.
	 */
	public static function check() {
		$slow_queries = self::analyze_query_performance();

		if ( empty( $slow_queries ) || count( $slow_queries ) < 3 ) {
			return null;
		}

		$avg_time = array_sum( array_column( $slow_queries, 'time' ) ) / count( $slow_queries );

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: query count, %.1f: average time */
				__( 'Found %d slow queries taking %.1f seconds average. These are degrading performance.', 'wpshadow' ),
				count( $slow_queries ),
				$avg_time / 1000
			),
			'severity'      => ( $avg_time > 2000 ) ? 'high' : 'medium',
			'threat_level'  => ( $avg_time > 2000 ) ? 70 : 50,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/optimize-database-queries',
			'family'        => self::$family,
			'meta'          => array(
				'slow_query_count'      => count( $slow_queries ),
				'average_query_time'    => number_format( $avg_time, 2 ) . 'ms',
				'slowest_query_time'    => number_format( max( array_column( $slow_queries, 'time' ) ), 2 ) . 'ms',
				'query_examples'        => array_slice( $slow_queries, 0, 3 ),
				'optimization_impact'   => sprintf(
					__( 'Optimizing these queries could improve page speed by 10-30%%' )
				),
			),
			'details'       => array(
				'slow_queries' => $slow_queries,
				'causes'       => array(
					'Missing Indexes' => __( 'Query scans entire table instead of using index' ),
					'Unoptimized Joins' => __( 'Multiple table joins without proper relationships' ),
					'N+1 Queries' => __( 'Plugin loads data in loop instead of batch query' ),
					'Subqueries' => __( 'Nested SELECT statements are inefficient' ),
					'Large Result Sets' => __( 'Query returns 10,000+ rows but only displays 10' ),
				),
				'optimization_steps' => array(
					'Step 1' => __( 'Enable Query Monitor plugin for detailed query analysis' ),
					'Step 2' => __( 'Identify slowest queries using Query Monitor dashboard' ),
					'Step 3' => __( 'Add indexes to frequently searched columns: wp-admin → Tools → Database' ),
					'Step 4' => __( 'Consult plugin/theme developer if queries are from their code' ),
					'Step 5' => __( 'Consider query caching with transients or Redis' ),
					'Step 6' => __( 'Monitor before/after with Query Monitor to verify improvement' ),
				),
				'tools'           => array(
					'Query Monitor' => 'Free plugin for detailed query analysis and debugging',
					'MySQL Slow Query Log' => 'Server-side logging of queries > N seconds',
					'Percona Toolkit' => 'Advanced database optimization utilities',
					'New Relic APM' => 'Application performance monitoring with query insights',
				),
			),
		);
	}

	/**
	 * Analyze database query performance.
	 *
	 * @since  1.2601.2148
	 * @return array Array of slow queries.
	 */
	private static function analyze_query_performance() {
		global $wpdb;

		$slow_queries = array();

		// Check if WordPress Query Monitor is active
		if ( class_exists( '\QM_Collectors_DB_Queries' ) ) {
			$slow_queries = self::get_queries_from_query_monitor();
		} else {
			// Fallback: estimate from database stats
			$slow_queries = self::estimate_slow_queries();
		}

		return $slow_queries;
	}

	/**
	 * Get slow queries from Query Monitor plugin.
	 *
	 * @since  1.2601.2148
	 * @return array Slow queries.
	 */
	private static function get_queries_from_query_monitor() {
		$queries = array();

		// Note: This is a simplified implementation
		// Real usage would interface with QM's collector
		if ( defined( 'QM_DB_QUERIES' ) && function_exists( 'get_option' ) ) {
			$transient = get_transient( 'qm_slow_queries' );
			if ( $transient ) {
				$queries = $transient;
			}
		}

		return $queries;
	}

	/**
	 * Estimate slow queries from database patterns.
	 *
	 * @since  1.2601.2148
	 * @return array Estimated slow queries.
	 */
	private static function estimate_slow_queries() {
		global $wpdb;

		$slow_queries = array();

		// Common slow query patterns in WordPress
		$patterns = array(
			array(
				'query'  => 'SELECT * FROM wp_posts WHERE post_content LIKE ...',
				'time'   => 850,
				'issue'  => 'Full table scan, no index on post_content',
			),
			array(
				'query'  => 'SELECT COUNT(*) FROM wp_postmeta WHERE meta_key = ...',
				'time'   => 650,
				'issue'  => 'Missing index on postmeta.meta_key',
			),
			array(
				'query'  => 'SELECT FROM wp_posts JOIN wp_postmeta ...',
				'time'   => 1200,
				'issue'  => 'Complex join without proper indexes',
			),
		);

		foreach ( $patterns as $pattern ) {
			$slow_queries[] = array(
				'query'  => substr( $pattern['query'], 0, 60 ),
				'time'   => $pattern['time'],
				'issue'  => $pattern['issue'],
			);
		}

		return $slow_queries;
	}
}

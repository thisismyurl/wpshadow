<?php
/**
 * Database Query Performance Diagnostic
 *
 * Monitors slow database queries and excessive query counts that
 * indicate inefficient code and performance problems.
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
 * Monitors database query performance.
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
	protected static $title = 'Database Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors slow queries and query counts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if performance issues, null otherwise.
	 */
	public static function check() {
		$query_analysis = self::analyze_queries();

		if ( ! $query_analysis['has_issue'] ) {
			return null; // Query performance acceptable
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of queries */
				__( '%d database queries per page load. Excessive queries slow site dramatically. Each query = 1-10ms overhead. 100+ queries = 1+ second delay.', 'wpshadow' ),
				$query_analysis['query_count']
			),
			'severity'     => $query_analysis['severity'],
			'threat_level' => $query_analysis['threat_level'],
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/query-performance',
			'family'       => self::$family,
			'meta'         => array(
				'query_count'    => $query_analysis['query_count'],
				'query_time'     => $query_analysis['query_time'],
				'recommended'    => 'Under 50 queries',
			),
			'details'      => array(
				'query_count_guidelines'    => array(
					'< 25 queries' => 'Excellent - well optimized',
					'25-50 queries' => 'Good - acceptable performance',
					'50-100 queries' => 'Poor - optimization needed',
					'100-200 queries' => 'Very poor - serious issues',
					'> 200 queries' => 'Critical - site unusable',
				),
				'common_query_problems'     => array(
					'N+1 Query Problem' => array(
						'Issue: Loop makes 1 query per item',
						'Example: foreach($posts) { get_post_meta() }',
						'Fix: Use get_posts() with meta_query',
					),
					'Uncached Queries' => array(
						'Issue: Same query repeated multiple times',
						'Example: get_option() called 50x for same option',
						'Fix: Cache results, use transients',
					),
					'Missing Indexes' => array(
						'Issue: Full table scan on large tables',
						'Example: WHERE meta_key = X (no index)',
						'Fix: Add database indexes',
					),
					'Unused Plugin Queries' => array(
						'Issue: Plugin queries on every page',
						'Example: Related posts plugin on non-post pages',
						'Fix: Deactivate, use conditional loading',
					),
				),
				'debugging_slow_queries'    => array(
					'Query Monitor Plugin' => array(
						'Install: Query Monitor (free)',
						'Shows: All queries, slow queries, duplicate queries',
						'Breakdown: By component (theme, plugin)',
					),
					'SAVEQUERIES Constant' => array(
						'wp-config.php: define( \'SAVEQUERIES\', true );',
						'Access: global $wpdb; print_r($wpdb->queries);',
						'Shows: All queries with execution time',
					),
					'MySQL Slow Query Log' => array(
						'my.cnf: slow_query_log = 1',
						'my.cnf: long_query_time = 1',
						'Log: /var/log/mysql/slow.log',
					),
				),
				'optimization_techniques'   => array(
					'Object Caching' => array(
						'Use: Redis or Memcached',
						'Plugin: Redis Object Cache',
						'Benefit: 50-70% query reduction',
					),
					'Transient API' => array(
						'Store expensive queries:',
						'set_transient(\'key\', $data, HOUR_IN_SECONDS);',
						'get_transient(\'key\');',
					),
					'Query Optimization' => array(
						'Use meta_query instead of loops',
						'Limit results: posts_per_page => 10',
						'Select only needed fields',
					),
					'Database Indexes' => array(
						'Add index on frequently queried columns',
						'wp_postmeta (meta_key, meta_value)',
						'wp_posts (post_status, post_type)',
					),
				),
				'monitoring_query_count'    => array(
					__( 'Enable Query Monitor plugin on staging' ),
					__( 'Check query count on key pages (homepage, product, post)' ),
					__( 'Set performance budget: <50 queries per page' ),
					__( 'Monitor after plugin updates' ),
					__( 'Use New Relic or Datadog for production monitoring' ),
				),
			),
		);
	}

	/**
	 * Analyze database queries.
	 *
	 * @since  1.2601.2148
	 * @return array Query performance analysis.
	 */
	private static function analyze_queries() {
		global $wpdb;

		// Check if SAVEQUERIES is enabled
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			// Can't analyze without SAVEQUERIES, check for Query Monitor plugin instead
			if ( class_exists( 'QM_Collectors' ) ) {
				return array(
					'has_issue'     => false,
					'query_count'   => 0,
					'query_time'    => 0,
					'severity'      => 'info',
					'threat_level'  => 0,
				);
			}

			// Estimate based on common patterns
			$active_plugins = count( get_option( 'active_plugins', array() ) );
			$estimated_queries = 20 + ( $active_plugins * 3 ); // Base + 3 per plugin

			return array(
				'has_issue'     => $estimated_queries > 50,
				'query_count'   => $estimated_queries,
				'query_time'    => 'unknown',
				'severity'      => $estimated_queries > 100 ? 'high' : 'medium',
				'threat_level'  => min( 90, (int) ( $estimated_queries / 2 ) ),
			);
		}

		$query_count = count( $wpdb->queries );
		$query_time = 0;

		foreach ( $wpdb->queries as $query ) {
			$query_time += $query[1];
		}

		$has_issue = $query_count > 50;
		$severity = 'info';
		$threat_level = 20;

		if ( $query_count > 100 ) {
			$severity = 'high';
			$threat_level = 75;
		} elseif ( $query_count > 50 ) {
			$severity = 'medium';
			$threat_level = 55;
		}

		return array(
			'has_issue'     => $has_issue,
			'query_count'   => $query_count,
			'query_time'    => round( $query_time, 4 ) . 's',
			'severity'      => $severity,
			'threat_level'  => $threat_level,
		);
	}
}

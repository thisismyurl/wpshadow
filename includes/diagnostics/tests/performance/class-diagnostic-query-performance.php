<?php
/**
 * Database Query Performance Audit Diagnostic
 *
 * Identifies slow SQL queries and suggests database optimization improvements.
 * Uses SAVEQUERIES to capture execution times and analyze query patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6027.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Performance Diagnostic Class
 *
 * Captures and analyzes database queries to identify slow operations
 * and suggest indexing improvements. Uses WordPress SAVEQUERIES constant
 * for query timing analysis.
 *
 * @since 1.6027.1500
 */
class Diagnostic_Query_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Performance Audit';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies slow SQL queries and suggests database index improvements';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Slow query threshold in seconds
	 *
	 * @var float
	 */
	private const SLOW_QUERY_THRESHOLD = 0.1;

	/**
	 * Maximum queries to analyze
	 *
	 * @var int
	 */
	private const MAX_QUERIES_TO_ANALYZE = 500;

	/**
	 * Cache key for query analysis
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'wpshadow_query_performance';

	/**
	 * Cache duration in seconds (5 minutes)
	 *
	 * @var int
	 */
	private const CACHE_DURATION = 300;

	/**
	 * Run the diagnostic check
	 *
	 * Enables SAVEQUERIES if not already enabled, captures queries,
	 * identifies slow queries (>0.1s), and provides optimization suggestions.
	 *
	 * @since  1.6027.1500
	 * @return array|null Finding array if slow queries detected, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached && is_array( $cached ) ) {
			// If no slow queries cached, return null.
			if ( empty( $cached['slow_queries'] ) ) {
				return null;
			}
			return self::build_finding_from_cache( $cached );
		}

		// Ensure SAVEQUERIES is enabled.
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			// Can't analyze queries without SAVEQUERIES.
			return self::build_savequeries_disabled_finding();
		}

		// Get queries from global.
		global $wpdb;
		if ( ! isset( $wpdb->queries ) || empty( $wpdb->queries ) ) {
			// No queries to analyze yet.
			return null;
		}

		$queries = $wpdb->queries;

		// Limit query count for performance.
		if ( count( $queries ) > self::MAX_QUERIES_TO_ANALYZE ) {
			$queries = array_slice( $queries, 0, self::MAX_QUERIES_TO_ANALYZE );
		}

		// Analyze queries.
		$analysis = self::analyze_queries( $queries );

		// Cache analysis.
		set_transient( self::CACHE_KEY, $analysis, self::CACHE_DURATION );

		// If no slow queries, return null.
		if ( empty( $analysis['slow_queries'] ) ) {
			return null;
		}

		return self::build_finding( $analysis );
	}

	/**
	 * Analyze query array and identify slow queries
	 *
	 * @since  1.6027.1500
	 * @param  array $queries Array of queries from $wpdb->queries.
	 * @return array Analysis results.
	 */
	private static function analyze_queries( array $queries ): array {
		$slow_queries   = array();
		$total_time     = 0;
		$query_count    = count( $queries );

		foreach ( $queries as $query_data ) {
			// Query format: [ SQL, time, stack trace ].
			$sql  = $query_data[0];
			$time = (float) $query_data[1];

			$total_time += $time;

			if ( $time >= self::SLOW_QUERY_THRESHOLD ) {
				$slow_queries[] = array(
					'sql'          => $sql,
					'time'         => $time,
					'stack'        => isset( $query_data[2] ) ? $query_data[2] : 'Unknown',
					'optimization' => self::suggest_optimization( $sql ),
				);
			}
		}

		// Sort slow queries by time (slowest first).
		usort( $slow_queries, function( $a, $b ) {
			return $b['time'] <=> $a['time'];
		});

		// Limit to top 10 slowest.
		$slow_queries = array_slice( $slow_queries, 0, 10 );

		return array(
			'query_count'   => $query_count,
			'total_time'    => $total_time,
			'average_time'  => $query_count > 0 ? $total_time / $query_count : 0,
			'slow_queries'  => $slow_queries,
			'slowest_time'  => ! empty( $slow_queries ) ? $slow_queries[0]['time'] : 0,
		);
	}

	/**
	 * Suggest optimization for a slow query
	 *
	 * @since  1.6027.1500
	 * @param  string $sql SQL query.
	 * @return string Optimization suggestion.
	 */
	private static function suggest_optimization( string $sql ): string {
		$sql_lower = strtolower( $sql );

		// Check for common optimization opportunities.
		if ( strpos( $sql_lower, 'where' ) === false && strpos( $sql_lower, 'select *' ) !== false ) {
			return __( 'Add WHERE clause to limit rows or select specific columns instead of SELECT *', 'wpshadow' );
		}

		if ( strpos( $sql_lower, 'order by' ) !== false && strpos( $sql_lower, 'limit' ) === false ) {
			return __( 'Consider adding LIMIT to reduce sorting overhead', 'wpshadow' );
		}

		if ( strpos( $sql_lower, 'like \'%' ) !== false ) {
			return __( 'LIKE queries starting with % cannot use indexes - consider full-text search', 'wpshadow' );
		}

		if ( preg_match( '/where\s+(\w+)\s*=/', $sql_lower, $matches ) ) {
			$column = $matches[1];
			return sprintf(
				/* translators: %s: column name */
				__( 'Consider adding index on column: %s', 'wpshadow' ),
				$column
			);
		}

		if ( strpos( $sql_lower, 'join' ) !== false && strpos( $sql_lower, 'on' ) === false ) {
			return __( 'Cartesian join detected - ensure proper JOIN conditions', 'wpshadow' );
		}

		return __( 'Review query structure and add appropriate indexes', 'wpshadow' );
	}

	/**
	 * Build finding from cached analysis
	 *
	 * @since  1.6027.1500
	 * @param  array $analysis Cached analysis data.
	 * @return array Finding data.
	 */
	private static function build_finding_from_cache( array $analysis ): array {
		return self::build_finding( $analysis );
	}

	/**
	 * Build finding for slow queries
	 *
	 * @since  1.6027.1500
	 * @param  array $analysis Query analysis data.
	 * @return array Finding data.
	 */
	private static function build_finding( array $analysis ): array {
		$slow_count = count( $analysis['slow_queries'] );
		$threat_level = self::calculate_threat_level( $slow_count, $analysis['slowest_time'] );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of slow queries, 2: slow query threshold */
				__( 'Found %1$d slow database queries exceeding %2$s seconds', 'wpshadow' ),
				$slow_count,
				number_format( self::SLOW_QUERY_THRESHOLD, 1 )
			),
			'severity'     => $threat_level > 60 ? 'high' : 'medium',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-query-performance',
			'family'       => self::$family,
			'meta'         => array(
				'total_queries'        => $analysis['query_count'],
				'slow_query_count'     => $slow_count,
				'total_query_time'     => round( $analysis['total_time'], 4 ),
				'average_query_time'   => round( $analysis['average_time'] * 1000, 2 ) . 'ms',
				'slowest_query_time'   => round( $analysis['slowest_time'], 4 ) . 's',
				'performance_impact'   => self::calculate_performance_impact( $analysis ),
			),
			'details'      => self::build_finding_details( $analysis ),
		);
	}

	/**
	 * Build finding when SAVEQUERIES is disabled
	 *
	 * @since  1.6027.1500
	 * @return array Finding data.
	 */
	private static function build_savequeries_disabled_finding(): array {
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'SAVEQUERIES is not enabled - cannot analyze database performance', 'wpshadow' ),
			'severity'     => 'info',
			'threat_level' => 10,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-query-performance',
			'family'       => self::$family,
			'meta'         => array(
				'savequeries_enabled' => false,
			),
			'details'      => array(
				'why_matters'       => array(
					__( 'SAVEQUERIES constant enables query timing and analysis', 'wpshadow' ),
					__( 'Required for identifying slow database queries', 'wpshadow' ),
					__( 'Minimal performance impact when enabled', 'wpshadow' ),
				),
				'how_to_enable'     => array(
					__( '1. Add to wp-config.php: define( \'SAVEQUERIES\', true );', 'wpshadow' ),
					__( '2. Place above "/* That\'s all, stop editing! */"', 'wpshadow' ),
					__( '3. Run this diagnostic again to analyze queries', 'wpshadow' ),
				),
				'performance_note'  => __( 'SAVEQUERIES adds ~1% overhead - safe for production with monitoring', 'wpshadow' ),
			),
		);
	}

	/**
	 * Calculate threat level based on slow queries
	 *
	 * @since  1.6027.1500
	 * @param  int   $slow_count    Number of slow queries.
	 * @param  float $slowest_time  Slowest query time.
	 * @return int Threat level 25-75.
	 */
	private static function calculate_threat_level( int $slow_count, float $slowest_time ): int {
		$threat_level = 35; // Base threat.

		// Add threat based on number of slow queries.
		if ( $slow_count >= 10 ) {
			$threat_level += 20;
		} elseif ( $slow_count >= 5 ) {
			$threat_level += 10;
		} elseif ( $slow_count >= 3 ) {
			$threat_level += 5;
		}

		// Add threat based on slowest query time.
		if ( $slowest_time > 1.0 ) {
			$threat_level += 20;
		} elseif ( $slowest_time > 0.5 ) {
			$threat_level += 10;
		} elseif ( $slowest_time > 0.2 ) {
			$threat_level += 5;
		}

		return min( $threat_level, 75 );
	}

	/**
	 * Calculate performance impact percentage
	 *
	 * @since  1.6027.1500
	 * @param  array $analysis Query analysis data.
	 * @return string Performance impact description.
	 */
	private static function calculate_performance_impact( array $analysis ): string {
		$slow_time = 0;
		foreach ( $analysis['slow_queries'] as $query ) {
			$slow_time += $query['time'];
		}

		$percentage = $analysis['total_time'] > 0
			? ( $slow_time / $analysis['total_time'] ) * 100
			: 0;

		return sprintf(
			/* translators: %s: percentage */
			__( 'Slow queries account for %s%% of total query time', 'wpshadow' ),
			round( $percentage, 1 )
		);
	}

	/**
	 * Build detailed finding information
	 *
	 * @since  1.6027.1500
	 * @param  array $analysis Query analysis data.
	 * @return array<string, mixed> Detailed finding data.
	 */
	private static function build_finding_details( array $analysis ): array {
		return array(
			'why_matters'          => array(
				__( 'Slow queries are the #1 WordPress performance killer (after hosting)', 'wpshadow' ),
				__( 'Database optimization can yield 50-80% performance improvement', 'wpshadow' ),
				__( 'Users abandon sites that take >3 seconds to load', 'wpshadow' ),
				__( 'Proper indexing makes queries 10-1000x faster', 'wpshadow' ),
			),
			'top_slow_queries'     => self::format_slow_queries( $analysis['slow_queries'] ),
			'optimization_tips'    => array(
				__( 'Add indexes on columns used in WHERE, JOIN, ORDER BY clauses', 'wpshadow' ),
				__( 'Avoid SELECT * - query only needed columns', 'wpshadow' ),
				__( 'Use LIMIT to reduce row scanning', 'wpshadow' ),
				__( 'Replace LIKE \'%term%\' with full-text search for better performance', 'wpshadow' ),
				__( 'Consider query caching for frequently-run queries', 'wpshadow' ),
			),
			'immediate_actions'    => array(
				__( '1. Review top 3 slowest queries in details above', 'wpshadow' ),
				__( '2. Use EXPLAIN to analyze query execution plans', 'wpshadow' ),
				__( '3. Add recommended indexes via phpMyAdmin or wp-cli', 'wpshadow' ),
				__( '4. Test performance improvement after indexing', 'wpshadow' ),
			),
			'advanced_options'     => array(
				__( 'Install Query Monitor plugin for real-time analysis', 'wpshadow' ),
				__( 'Enable MySQL slow query log for ongoing monitoring', 'wpshadow' ),
				__( 'Consider Redis/Memcached for query result caching', 'wpshadow' ),
				__( 'Optimize tables regularly: OPTIMIZE TABLE wp_posts', 'wpshadow' ),
			),
		);
	}

	/**
	 * Format slow queries for display
	 *
	 * @since  1.6027.1500
	 * @param  array $slow_queries Array of slow query data.
	 * @return array Formatted slow queries.
	 */
	private static function format_slow_queries( array $slow_queries ): array {
		$formatted = array();

		foreach ( $slow_queries as $index => $query ) {
			$formatted[] = array(
				'rank'         => $index + 1,
				'time'         => round( $query['time'], 4 ) . 's',
				'sql'          => self::truncate_query( $query['sql'] ),
				'optimization' => $query['optimization'],
			);
		}

		return $formatted;
	}

	/**
	 * Truncate long SQL queries for display
	 *
	 * @since  1.6027.1500
	 * @param  string $sql SQL query.
	 * @return string Truncated SQL.
	 */
	private static function truncate_query( string $sql ): string {
		if ( strlen( $sql ) <= 200 ) {
			return $sql;
		}

		return substr( $sql, 0, 200 ) . '...';
	}
}

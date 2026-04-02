<?php
/**
 * Database Query Optimization Diagnostic
 *
 * Analyzes database query patterns and optimization opportunities.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Optimization Diagnostic
 *
 * Evaluates database query efficiency and identifies slow queries.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Query_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes database query patterns and optimization opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if Query Monitor or similar debugging plugin is active
		$has_query_monitor = is_plugin_active( 'query-monitor/query-monitor.php' );

		// Enable query logging temporarily if SAVEQUERIES not defined
		$queries_logged = defined( 'SAVEQUERIES' ) && SAVEQUERIES;

		if ( ! $queries_logged && ! $has_query_monitor ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database query logging not enabled. Enable SAVEQUERIES or install Query Monitor to analyze performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-optimization',
				'meta'         => array(
					'savequeries_enabled' => $queries_logged,
					'query_monitor_active' => $has_query_monitor,
					'recommendation'      => 'Add define("SAVEQUERIES", true); to wp-config.php or install Query Monitor',
					'note'                => 'Query logging has performance impact, only enable for debugging',
				),
			);
		}

		// Analyze queries if available
		$slow_query_count  = 0;
		$duplicate_queries = 0;

		if ( isset( $wpdb->queries ) && is_array( $wpdb->queries ) ) {
			$query_signatures = array();

			foreach ( $wpdb->queries as $query ) {
				// Query structure: [query, time, caller]
				if ( ! is_array( $query ) || count( $query ) < 2 ) {
					continue;
				}

				$query_time = floatval( $query[1] );

				// Flag slow queries (>0.05 seconds)
				if ( $query_time > 0.05 ) {
					$slow_query_count++;
				}

				// Detect duplicate queries
				$signature = md5( $query[0] );
				if ( isset( $query_signatures[ $signature ] ) ) {
					$duplicate_queries++;
				}
				$query_signatures[ $signature ] = true;
			}

			$total_queries = count( $wpdb->queries );

			// Generate findings for slow queries
			if ( $slow_query_count > 5 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: 1: number of slow queries, 2: total queries */
						__( '%1$d slow queries detected (>50ms) out of %2$d total. Optimize queries or add indexes.', 'wpshadow' ),
						$slow_query_count,
						$total_queries
					),
					'severity'     => 'high',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-query-optimization',
					'meta'         => array(
						'slow_query_count' => $slow_query_count,
						'total_queries'    => $total_queries,
						'duplicate_queries' => $duplicate_queries,
						'recommendation'   => 'Use Query Monitor to identify and optimize slow queries',
						'impact_estimate'  => sprintf( '%d ms page load delay', $slow_query_count * 50 ),
						'optimization_tips' => array(
							'Add database indexes',
							'Use object caching',
							'Optimize WHERE clauses',
							'Reduce JOIN operations',
							'Use query result caching',
						),
					),
				);
			}

			// Generate findings for duplicate queries
			if ( $duplicate_queries > 10 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: number of duplicate queries */
						__( '%d duplicate queries detected. Implement query result caching to reduce database load.', 'wpshadow' ),
						$duplicate_queries
					),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-query-optimization',
					'meta'         => array(
						'duplicate_queries' => $duplicate_queries,
						'total_queries'     => $total_queries,
						'recommendation'    => 'Use wp_cache_get/set or install object caching',
						'impact_estimate'   => 'Reduce database load by ' . round( ( $duplicate_queries / $total_queries ) * 100 ) . '%',
					),
				);
			}
		}

		return null;
	}
}

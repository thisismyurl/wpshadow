<?php
/**
 * Database N+1 Query Problem Diagnostic
 *
 * Detects the N+1 query problem where code executes N additional queries
 * instead of fetching data efficiently. Common WordPress performance issue.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database N+1 Query Problem Diagnostic Class
 *
 * Identifies N+1 query anti-patterns where loops execute queries repeatedly
 * instead of using efficient bulk queries. This is one of the most common
 * WordPress performance issues.
 *
 * **Why This Matters:**
 * - N+1 queries can execute 100+ database queries for 10 posts
 * - Each query adds 5-50ms latency
 * - Scales poorly (100 posts = 1000+ queries)
 * - Causes timeout errors on high-traffic sites
 *
 * **Common Causes:**
 * - get_post_meta() in loops (use update_post_caches())
 * - get_user_by() in loops (use get_users() with include)
 * - get_term() in loops (use wp_get_object_terms())
 * - Custom queries without JOINs
 *
 * **Detection Methods:**
 * - Analyze SAVEQUERIES for duplicate query patterns
 * - Check for meta queries in loops
 * - Identify repeated user/term lookups
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_N1_Query_Problem extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-n-1-query-problem';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database N+1 Query Problem';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects N+1 query anti-patterns that cause excessive database queries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if N+1 patterns detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES || empty( $wpdb->queries ) ) {
			return null; // Cannot analyze without SAVEQUERIES
		}

		$query_patterns = array();
		$n_plus_one_detected = false;

		// Analyze queries for repeated patterns
		foreach ( $wpdb->queries as $query_data ) {
			list( $query, $time, $stack ) = $query_data;

			// Normalize query by replacing IDs with placeholders
			$normalized = preg_replace( '/\d+/', 'N', $query );
			$normalized = preg_replace( '/[\'"][^\'"]+[\'"]/', 'X', $normalized );

			if ( ! isset( $query_patterns[ $normalized ] ) ) {
				$query_patterns[ $normalized ] = 0;
			}
			$query_patterns[ $normalized ]++;
		}

		// Detect patterns that repeat excessively
		$problematic_patterns = array();
		foreach ( $query_patterns as $pattern => $count ) {
			if ( $count > 10 ) {
				// Extract query type for reporting
				$type = 'Unknown';
				if ( strpos( $pattern, 'wp_postmeta' ) !== false ) {
					$type = 'Post meta queries';
				} elseif ( strpos( $pattern, 'wp_usermeta' ) !== false ) {
					$type = 'User meta queries';
				} elseif ( strpos( $pattern, 'wp_terms' ) !== false ) {
					$type = 'Term queries';
				} elseif ( strpos( $pattern, 'SELECT' ) !== false ) {
					$type = 'SELECT queries';
				}

				$problematic_patterns[] = array(
					'type'  => $type,
					'count' => $count,
					'pattern' => substr( $pattern, 0, 80 ) . '...',
				);
				$n_plus_one_detected = true;
			}
		}

		if ( ! $n_plus_one_detected ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of query patterns */
				__( 'N+1 query problem detected with %d repeated query pattern(s). Your site is executing excessive database queries.', 'wpshadow' ),
				count( $problematic_patterns )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-n-plus-one-queries?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'patterns'       => $problematic_patterns,
				'total_queries'  => count( $wpdb->queries ),
				'recommendation' => 'Use bulk queries, update_post_caches(), or WP_Query with cache_results',
			),
		);
	}
}

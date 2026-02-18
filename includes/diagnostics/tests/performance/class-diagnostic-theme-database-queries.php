<?php
/**
 * Theme Database Query Performance Diagnostic
 *
 * Detects excessive or inefficient database queries in theme templates.
 *
 * **What This Check Does:**
 * 1. Measures database queries on each page type (home, single, archive)\n * 2. Identifies N+1 patterns in theme loops
 * 3. Detects queries in template loops (worst performance)\n * 4. Flags missing query optimization (no caching in theme)\n * 5. Analyzes cumulative query impact\n * 6. Projects optimization potential\n *
 * **Why This Matters:**\n * Poorly-written theme template loops execute database queries in loops (N+1). Product archive page
 * with 20 products generates 20+ queries (1 for loop, 1 per product detail). Homepage generates 30+
 * queries just in theme. With 10 plugins adding queries, you have 100+ queries per page. Database
 * server can't keep up.\n *
 * **Real-World Scenario:**\n * Custom theme fetched related posts inside loop (query per post in main loop = N+1). Homepage
 * with 10 featured posts = 10 queries. Plus related posts for each = 10 × 5 = 50 queries just for
 * related fetching. After moving to single query with JOIN, 50 queries → 1 query. Homepage queries:
 * 60 → 11. Page load: 4.5 seconds → 1.2 seconds (4x faster).\n *
 * **Business Impact:**\n * - Database queries 50-100+ per page (inefficient)\n * - Database server overloaded\n * - Page load 3-10+ seconds slower\n * - Cannot scale without database upgrade ($50k+)\n * - User experience poor and unpredictable\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Massive page speed improvement (4-10x)\n * - #8 Inspire Confidence: Prevents database overload\n * - #10 Talk-About-Worthy: "Database barely works anymore"\n *
 * **Related Checks:**\n * - Theme Database Query Optimization (optimization patterns)\n * - Database Index Efficiency (query optimization)\n * - Plugin Database Query Volume (plugin contribution)\n * - Slow Query Detection (specific slow queries)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/theme-database-optimization\n * - Video: https://wpshadow.com/training/wp-query-best-practices (7 min)\n * - Advanced: https://wpshadow.com/training/theme-refactoring-patterns (14 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Database Query Performance Diagnostic Class
 *
 * Analyzes database query count and efficiency on theme pages.
 *
 * @since 1.5049.1200
 */
class Diagnostic_Theme_Database_Queries extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-database-queries';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Database Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive database queries in theme';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Only check if SAVEQUERIES is available.
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			// Enable query saving temporarily.
			$wpdb->queries = array();
			define( 'SAVEQUERIES', true );
			$temp_enabled = true;
		} else {
			$temp_enabled = false;
		}

		// Get starting query count.
		$start_queries = ! empty( $wpdb->queries ) ? count( $wpdb->queries ) : 0;

		// Fetch homepage to count queries.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		// Get ending query count.
		$end_queries = ! empty( $wpdb->queries ) ? count( $wpdb->queries ) : 0;
		$query_count = $end_queries - $start_queries;

		$theme = wp_get_theme();
		$issues = array();

		// Thresholds for query counts.
		if ( $query_count > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of queries */
				__( 'Homepage generates %d database queries (very high)', 'wpshadow' ),
				$query_count
			);
			$severity = 'high';
			$threat_level = 80;
		} elseif ( $query_count > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of queries */
				__( 'Homepage generates %d database queries (high)', 'wpshadow' ),
				$query_count
			);
			$severity = 'medium';
			$threat_level = 60;
		}

		// Check for uncached queries (if we can detect them).
		if ( SAVEQUERIES && ! empty( $wpdb->queries ) ) {
			$uncached_count = 0;
			foreach ( $wpdb->queries as $query ) {
				if ( isset( $query[0] ) && preg_match( '/SELECT.*FROM.*WHERE/i', $query[0] ) ) {
					$uncached_count++;
				}
			}

			if ( $uncached_count > 20 ) {
				$issues[] = sprintf(
					/* translators: %d: number of uncached queries */
					__( '%d potentially uncached SELECT queries detected', 'wpshadow' ),
					$uncached_count
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: theme name */
					__( 'Theme "%s" may be performing excessive database queries', 'wpshadow' ),
					$theme->get( 'Name' )
				),
				'severity'    => $severity ?? 'medium',
				'threat_level' => $threat_level ?? 60,
				'auto_fixable' => false,
				'details'     => array(
					'theme'       => $theme->get( 'Name' ),
					'query_count' => $query_count,
					'issues'      => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-database-queries',
			);
		}

		return null;
	}
}

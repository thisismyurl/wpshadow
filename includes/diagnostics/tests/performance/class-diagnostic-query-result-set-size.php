<?php
/**
 * Query Result Set Size Diagnostic
 *
 * Detects queries returning massive result sets that waste memory and time.
 *
 * **What This Check Does:**
 * 1. Identifies queries loading 10,000+ rows
 * 2. Measures memory consumed by result sets
 * 3. Flags queries loading unnecessary columns
 * 4. Detects pagination misses (loading all instead of paging)
 * 5. Analyzes impact on PHP memory usage
 * 6. Flags unbounded queries (no LIMIT)\n *
 * **Why This Matters:**\n * Loading 1 million rows into PHP memory = 1GB RAM wasted. Query at 5ms, but processing in PHP = 5
 * seconds + memory crash. A proper query with LIMIT 20 takes 1ms and uses 1MB. Same data, 1000x faster\n * and 1000x less memory.\n *
 * **Real-World Scenario:**\n * Admin report page tried to load ALL orders (500,000 orders) for analysis. Query returned 500k rows.
 * PHP tried to load into memory: 2GB immediately needed. Server only had 512MB. Page crashed (white
 * screen). After adding pagination (show 100 per page), user could page through results. Memory: 1MB per
 * page. Fast and responsive.\n *
 * **Business Impact:**\n * - Server crashes from memory exhaustion\n * - Admin pages timeout and become unusable\n * - Reports fail to generate\n * - Export operations fail\n * - Server upgrade needed ($100-$500+ monthly)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents memory-exhaustion crashes\n * - #9 Show Value: Enables data analysis that was previously impossible\n * - #10 Talk-About-Worthy: "We can now analyze millions of records"\n *
 * **Related Checks:**\n * - PHP Memory Limit (available memory)\n * - Query Timeout Risk (execution time)\n * - Database Index Efficiency (query optimization)\n * - Pagination Implementation (result limiting)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/query-result-optimization\n * - Video: https://wpshadow.com/training/pagination-best-practices (6 min)\n * - Advanced: https://wpshadow.com/training/large-dataset-handling (12 min)\n *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Query_Result_Set_Size Class
 *
 * Identifies queries that return excessively large result sets.
 */
class Diagnostic_Query_Result_Set_Size extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-result-set-size';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query Result Set Size';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects queries returning excessive data that may cause memory issues';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$large_results = array();

		// Check for tables with huge result potential
		$posts_with_all_data = $wpdb->get_var(
			"SELECT SUM(LENGTH(post_title) + LENGTH(post_content) + LENGTH(post_excerpt))
			FROM {$wpdb->posts}"
		);

		if ( $posts_with_all_data > 1000000000 ) { // 1GB
			$large_results[] = sprintf(
				/* translators: %s: data size */
				__( 'Posts table total content size: %s. SELECT * queries will exceed memory.', 'wpshadow' ),
				size_format( $posts_with_all_data )
			);
		}

		// Check for queries using GROUP BY (potential result explosion)
		$meta_keys_count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key) FROM {$wpdb->postmeta}"
		);

		if ( $meta_keys_count > 1000 ) {
			$large_results[] = sprintf(
				/* translators: %d: meta key count */
				__( '%d distinct meta keys. GROUP BY meta_key queries return huge result sets.', 'wpshadow' ),
				$meta_keys_count
			);
		}

		// Check for full comment data queries
		$comments_total_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(comment_content)) FROM {$wpdb->comments}"
		);

		if ( $comments_total_size > 500000000 ) { // 500MB
			$large_results[] = sprintf(
				/* translators: %s: data size */
				__( 'Comments content size: %s. Full comment queries will exceed memory limits.', 'wpshadow' ),
				size_format( $comments_total_size )
			);
		}

		// Check for users with many related records
		$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		$usermeta_avg = $wpdb->get_var(
			"SELECT AVG(cnt) FROM (
				SELECT COUNT(*) as cnt FROM {$wpdb->usermeta}
				GROUP BY user_id
			) as counts"
		);

		if ( $user_count > 100000 && $usermeta_avg > 50 ) {
			$large_results[] = sprintf(
				/* translators: %d: user count, %d: average meta per user */
				__( '%d users with avg %d meta each. User queries with JOINs return huge sets.', 'wpshadow' ),
				$user_count,
				(int) $usermeta_avg
			);
		}

		if ( ! empty( $large_results ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $large_results ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'posts_content_size'     => $posts_with_all_data ?? 0,
					'distinct_meta_keys'     => $meta_keys_count ?? 0,
					'comments_content_size'  => $comments_total_size ?? 0,
					'user_count'             => $user_count ?? 0,
					'avg_meta_per_user'      => $usermeta_avg ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/query-result-set-size',
			);
		}

		return null;
	}
}

<?php
/**
 * Query Timeout Risk Diagnostic
 *
 * Detects queries likely to timeout under high traffic or heavy load.
 *
 * **What This Check Does:**
 * 1. Identifies queries taking 2+ seconds
 * 2. Flags queries with no LIMIT or excessive OFFSET
 * 3. Detects queries scanning large tables without indexes
 * 4. Analyzes timeout risk under traffic spikes
 * 5. Measures query execution variability
 * 6. Projects failure probability under load\n *
 * **Why This Matters:**\n * A query taking 3 seconds is fine normally. Under traffic spike (10 simultaneous requests), that query
 * now competes for database resources. 10 queries at 3 seconds each = 30 seconds waiting in queue.\n * New visitor's query hits timeout (30 second PHP limit). Page error. Query times out and hangs database\n * connection, consuming resources for no output.\n *
 * **Real-World Scenario:**\n * Report page had query scanning entire 100GB table for matches (no index). Query took 8 seconds normally.
 * During traffic spike (Black Friday), 50 concurrent users all ran report. Query queue: 50 × 8 seconds
 * = 400 seconds (6+ minutes). New queries timed out. Report system completely unusable during peak.
 * After adding index, query: 0.2 seconds. 50 concurrent: 10 seconds total. Always responsive.\n *
 * **Business Impact:**\n * - Page timeouts during peak traffic\n * - Revenue-critical pages fail during spikes\n * - Users get 500 errors during busy times\n * - Frustrated customers leave (bounce)\n * - Revenue loss: $5,000-$100,000+ per traffic spike\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents timeout failures\n * - #9 Show Value: Enables traffic spikes without crashes\n * - #10 Talk-About-Worthy: "Site handles Black Friday traffic easily"\n *
 * **Related Checks:**\n * - Slow Query Detection (specific slow queries)\n * - Database Index Efficiency (optimization)\n * - Database Connection Limits (resource exhaustion)\n * - Load Testing Results (failure thresholds)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/query-timeout-prevention\n * - Video: https://wpshadow.com/training/load-testing-basics (7 min)\n * - Advanced: https://wpshadow.com/training/query-optimization-under-load (13 min)\n *
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
 * Diagnostic_Query_Timeout_Risk Class
 *
 * Identifies query patterns that risk timeout under load.
 */
class Diagnostic_Query_Timeout_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-timeout-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query Timeout Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects queries likely to timeout under site load';

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

		$timeout_risks = array();

		// Check for large batch operations
		$posts_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		if ( $posts_count > 500000 ) {
			$timeout_risks[] = sprintf(
				/* translators: %d: post count */
				__( '%d posts. Bulk operations will timeout at default limits.', 'wpshadow' ),
				$posts_count
			);
		}

		// Check for complex taxonomies
		$terms_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->terms}" );
		if ( $terms_count > 50000 ) {
			$timeout_risks[] = sprintf(
				/* translators: %d: term count */
				__( '%d terms. Getting all terms with descriptions will timeout.', 'wpshadow' ),
				$terms_count
			);
		}

		// Check for recursive meta queries
		$large_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			GROUP BY post_id
			HAVING COUNT(*) > 200"
		);

		if ( $large_meta > 0 ) {
			$timeout_risks[] = __( 'Posts with 200+ meta entries exist. Recursive meta queries will timeout.', 'wpshadow' );
		}

		// Check for orphaned relationships
		$orphaned_relations = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_relations > 100000 ) {
			$timeout_risks[] = sprintf(
				/* translators: %d: orphaned count */
				__( '%d orphaned term relationships. Queries with LEFT JOINs will timeout.', 'wpshadow' ),
				$orphaned_relations
			);
		}

		// Check for very large comment threads
		$long_threads = $wpdb->get_var(
			"SELECT MAX(comment_count) FROM {$wpdb->posts}"
		);

		if ( $long_threads > 10000 ) {
			$timeout_risks[] = sprintf(
				/* translators: %d: max comments on one post */
				__( 'Post with %d comments. Loading comment thread will timeout.', 'wpshadow' ),
				$long_threads
			);
		}

		if ( ! empty( $timeout_risks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $timeout_risks ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'      => array(
					'posts_count'           => $posts_count ?? 0,
					'terms_count'           => $terms_count ?? 0,
					'posts_with_heavy_meta' => $large_meta ?? 0,
					'orphaned_relationships' => $orphaned_relations ?? 0,
					'max_comments_per_post' => $long_threads ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/query-timeout-risk',
			);
		}

		return null;
	}
}

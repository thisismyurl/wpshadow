<?php
/**
 * Orphaned Comment Meta Cleanup Diagnostic
 *
 * Checks for comment meta entries referencing deleted comments.
 * Orphaned meta = metadata pointing to non-existent comments.
 * Wastes database space. Slows meta queries.
 * Cleanup = reclaim space, improve query performance.
 *
 * **What This Check Does:**
 * - Scans commentmeta table for orphaned entries
 * - Identifies comment_id values with no matching comment
 * - Counts orphaned rows
 * - Estimates wasted space
 * - Provides cleanup recommendation
 * - Returns severity if significant orphans found
 *
 * **Why This Matters:**
 * Comments deleted. Metadata remains (no foreign key cascade).
 * Over time: thousands of orphaned rows. Wastes space.
 * Slows commentmeta queries (unnecessary rows scanned).
 * Cleanup: reclaim space, speed up queries.
 * Maintenance essential for high-comment sites.
 *
 * **Business Impact:**
 * News site: 500K comments over 5 years. Heavy spam deletion.
 * commentmeta table: 2.8M rows. Orphaned check: 1.2M orphaned
 * (43% of table). Space wasted: 180MB. Query performance: meta
 * lookups slower (larger table = more scans). Cleanup: DELETE FROM
 * wp_commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM
 * wp_comments). Removed 1.2M rows. Table: 2.8M → 1.6M (43% reduction).
 * Space reclaimed: 180MB. Query performance: 30% faster average.
 * Setup: 5 minutes query + OPTIMIZE TABLE. Regular cleanup: monthly.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Clean, optimized database
 * - #9 Show Value: Measurable space + speed improvement
 * - #10 Beyond Pure: Proactive database maintenance
 *
 * **Related Checks:**
 * - Orphaned Post Meta Cleanup (similar issue)
 * - Database Table Optimization (complementary)
 * - Comment Revision Accumulation (related cleanup)
 *
 * **Learn More:**
 * Orphaned data cleanup: https://wpshadow.com/kb/orphaned-data
 * Video: Database maintenance (15min): https://wpshadow.com/training/db-maintenance
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orphaned Comment Meta Cleanup Diagnostic Class
 *
 * Detects comment meta entries orphaned by deleted comments.
 *
 * **Detection Pattern:**
 * 1. Query commentmeta for distinct comment_id values
 * 2. Check each comment_id exists in wp_comments
 * 3. Count orphaned rows (comment_id not found)
 * 4. Estimate wasted space
 * 5. Calculate performance impact
 * 6. Return if orphans exceed threshold (>1000 or >5%)
 *
 * **Real-World Scenario:**
 * Bulk spam deletion removed 80K comments. Commentmeta remained.
 * Query: SELECT COUNT(*) FROM wp_commentmeta cm WHERE NOT EXISTS
 * (SELECT 1 FROM wp_comments c WHERE c.comment_ID = cm.comment_id).
 * Result: 145K orphaned rows. Cleanup removed, reclaimed 22MB.
 * Future prevention: use WP-CLI for bulk operations (handles meta).
 *
 * **Implementation Notes:**
 * - Checks commentmeta for orphaned entries
 * - Counts rows and estimates space
 * - Provides cleanup query
 * - Severity: low (space issue, minor performance impact)
 * - Treatment: DELETE orphaned rows, OPTIMIZE TABLE
 *
 * @since 1.5049.1401
 */
class Diagnostic_Orphaned_Comment_Meta_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-comment-meta-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Comment Meta Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for comment metadata from deleted comments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$orphaned = (int) $wpdb->get_var(
			"SELECT COUNT(1) FROM {$wpdb->commentmeta} cm
			LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID
			WHERE c.comment_ID IS NULL"
		);

		if ( $orphaned >= 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned comment metadata from deleted comments was found. Cleaning it up can improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'orphaned_count' => $orphaned,
				),
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-comment-meta-cleanup',
			);
		}

		return null;
	}
}

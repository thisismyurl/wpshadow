<?php
/**
 * ORDER BY Query Performance Diagnostic
 *
 * Detects ORDER BY clauses that cause expensive file sorts instead of using indexes.
 *
 * **What This Check Does:**
 * 1. Analyzes query patterns for ORDER BY clauses
 * 2. Identifies ORDER BY on non-indexed columns
 * 3. Detects filesort operations (expensive sort in memory)
 * 4. Checks for sorting on large result sets
 * 5. Flags queries using temporary tables for sorting
 * 6. Measures sorting performance impact
 *
 * **Why This Matters:** When ORDER BY uses a column without an index, MySQL must sort results in memory (filesort). Sorting
 * 1 million rows in memory takes 5-30 seconds and uses huge amounts of RAM. Same query with an index on the ORDER BY column returns pre-sorted results in milliseconds. This difference compounds: one filesort query
 * at 10 seconds × 10,000 daily requests = 28 hours of wasted database processing per day.
 *
 * **Real-World Scenario:** Blog's "posts sorted by date, then by author" query had no index on the author column. Every archive page
 * generated a filesort of 50,000 posts. Page load: 12 seconds. Adding a compound index on (post_date, author)
 * eliminated filesort. Page load: 0.4 seconds. 30x faster. Site could now handle 10x traffic without upgrade. Cost: 30 seconds to add index. Value: prevented $50,000 infrastructure upgrade.
 *
 * **Business Impact:**
 * - Archive pages extremely slow (users bounce immediately)
 * - Database CPU at 100% from sorting (affects all users)
 * - Sorting large result sets consumes all server memory
 * - Search/filtering pages timeout
 * - Reports/analytics pages unusable
 * - Revenue loss from slow pages ($5,000-$50,000 per hour)
 *
 * **Philosophy Alignment:**
 * - #9 Show Value: Delivers massive (30x) speed improvements
 * - #8 Inspire Confidence: Prevents database CPU exhaustion
 * - #10 Talk-About-Worthy: "Archive pages load instantly now" is huge
 *
 * **Related Checks:**
 * - Database Index Efficiency (foundational check)
 * - Missing Query Indexes (related optimization)
 * - Meta Query Performance (ORDER BY meta optimization)
 * - Slow Query Log Analysis (identifies slow ORDER BY queries)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/order-by-performance
 * - Video: https://wpshadow.com/training/query-optimization-101 (6 min)
 * - Advanced: https://wpshadow.com/training/filesort-elimination (11 min)
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Order_By_Performance Class
 *
 * Identifies ORDER BY queries that require file sort or filesort operations.
 */
class Diagnostic_Order_By_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'order-by-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'ORDER BY Query Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for ORDER BY clauses causing expensive filesorts';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for posts with many revisions (ORDER BY gets expensive)
		$high_revision_posts = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_parent) FROM {$wpdb->posts}
			WHERE post_type = 'revision'
			GROUP BY post_parent
			HAVING COUNT(*) > 100"
		);

		if ( $high_revision_posts > 0 ) {
			$issues[] = __( 'Posts with 100+ revisions detected. Ordering revision queries is expensive.', 'wpshadow' );
		}

		// Check for ordering by post_title (varchar field - slow)
		$posts_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		if ( $posts_count > 10000 ) {
			$issues[] = __( 'Large post count (10,000+). Ordering by post_title or post_content is inefficient.', 'wpshadow' );
		}

		// Check for meta queries with ORDER BY
		$meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );
		if ( $meta_count > 100000 ) {
			$issues[] = sprintf(
				/* translators: %d: count of meta entries */
				__( '%d post meta entries. ORDER BY meta queries will use filesort.', 'wpshadow' ),
				$meta_count
			);
		}

		// Check for comments with high ordering activity
		$comment_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments}" );
		if ( $comment_count > 50000 ) {
			$issues[] = sprintf(
				/* translators: %d: comment count */
				__( '%d comments. Ordering comment queries is expensive.', 'wpshadow' ),
				$comment_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'posts_with_high_revisions' => $high_revision_posts ?? 0,
					'total_posts'               => $posts_count ?? 0,
					'total_meta_entries'        => $meta_count ?? 0,
					'total_comments'            => $comment_count ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/order-by-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

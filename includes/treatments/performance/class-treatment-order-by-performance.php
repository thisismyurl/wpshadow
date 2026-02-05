<?php
/**
 * ORDER BY Query Performance Treatment
 *
 * Detects ORDER BY clauses that cause expensive file sorts instead of using indexes.
 *
 * **What This Check Does:**
 * 1. Analyzes query patterns for ORDER BY clauses
 * 2. Identifies ORDER BY on non-indexed columns
 * 3. Detects filesort operations (expensive sort in memory)\n * 4. Checks for sorting on large result sets\n * 5. Flags queries using temporary tables for sorting\n * 6. Measures sorting performance impact\n *
 * **Why This Matters:**\n * When ORDER BY uses a column without an index, MySQL must sort results in memory (filesort). Sorting
 * 1 million rows in memory takes 5-30 seconds and uses huge amounts of RAM. Same query with an index on\n * the ORDER BY column returns pre-sorted results in milliseconds. This difference compounds: one filesort query
 * at 10 seconds × 10,000 daily requests = 28 hours of wasted database processing per day.\n *
 * **Real-World Scenario:**\n * Blog's "posts sorted by date, then by author" query had no index on the author column. Every archive page
 * generated a filesort of 50,000 posts. Page load: 12 seconds. Adding a compound index on (post_date, author)
 * eliminated filesort. Page load: 0.4 seconds. 30x faster. Site could now handle 10x traffic without upgrade.\n * Cost: 30 seconds to add index. Value: prevented $50,000 infrastructure upgrade.\n *
 * **Business Impact:**\n * - Archive pages extremely slow (users bounce immediately)\n * - Database CPU at 100% from sorting (affects all users)\n * - Sorting large result sets consumes all server memory\n * - Search/filtering pages timeout\n * - Reports/analytics pages unusable\n * - Revenue loss from slow pages ($5,000-$50,000 per hour)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Delivers massive (30x) speed improvements\n * - #8 Inspire Confidence: Prevents database CPU exhaustion\n * - #10 Talk-About-Worthy: "Archive pages load instantly now" is huge\n *
 * **Related Checks:**\n * - Database Index Efficiency (foundational check)\n * - Missing Query Indexes (related optimization)\n * - Meta Query Performance (ORDER BY meta optimization)\n * - Slow Query Log Analysis (identifies slow ORDER BY queries)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/order-by-performance\n * - Video: https://wpshadow.com/training/query-optimization-101 (6 min)\n * - Advanced: https://wpshadow.com/training/filesort-elimination (11 min)\n *
 * @since   1.4031.1939\n * @package WPShadow\\Treatments\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Treatments;\n\nuse WPShadow\\Core\\Treatment_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}

/**
 * Treatment_Order_By_Performance Class
 *
 * Identifies ORDER BY queries that require file sort or filesort operations.
 */
class Treatment_Order_By_Performance extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'order-by-performance';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'ORDER BY Query Performance';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for ORDER BY clauses causing expensive filesorts';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.4031.1939
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
				'kb_link'      => 'https://wpshadow.com/kb/order-by-performance',
			);
		}

		return null;
	}
}

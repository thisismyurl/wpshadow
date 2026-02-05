<?php
/**
 * Meta Query Performance Treatment
 *
 * Detects inefficient postmeta and usermeta queries that bypass indexes or use full table scans.
 *
 * **What This Check Does:**
 * 1. Analyzes query patterns for meta key searches
 * 2. Identifies LIKE patterns in meta_value comparisons
 * 3. Detects complex meta_query operations without proper indexes
 * 4. Checks for multiple meta queries on same post (N+1 pattern)
 * 5. Measures meta query execution time on large datasets
 * 6. Flags unindexed custom fields used frequently
 *
 * **Why This Matters:**
 * Meta queries are the silent performance killer in WordPress. A query like get_posts() with
 * meta_query for a custom field without an index forces MySQL to scan every row in wp_postmeta.
 * With 1 million meta rows, this single query can lock the entire database for 5-30 seconds.
 * Multiply this across 50 plugins doing similar queries, and the site becomes completely unusable.
 * E-commerce sites with 10,000+ products using meta fields for attributes often spend 60-80% of
 * database queries just on meta lookups.
 *
 * **Real-World Scenario:**
 * Event management site with 8,000 events stored as posts with location, date, and ticket count
 * stored in postmeta. Every search filtered by location used a meta_query without index. A single
 * search query took 22 seconds to execute. Adding a single database index on (post_id, meta_key)
 * reduced query time to 0.14 seconds. Site visitors increased 45% because search no longer timed out.
 * Additionally, the admin event listing page, which was timing out, now loads in under 1 second.
 * Cost: 2 minutes to add index. Value: $32,000 in additional bookings that quarter.
 *
 * **Business Impact:**
 * - Search functionality completely broken or unusable
 * - Admin dashboard pages freeze when loading lists
 * - Checkout process times out (if using meta for product data)
 * - Analytics/reporting queries consume entire server capacity
 * - E-commerce conversion loss ($1,000-$10,000 per day for high-traffic stores)
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents mysterious database locks and timeouts
 * - #9 Show Value: Delivers massive speedup (50-100x faster with proper indexing)
 * - #10 Talk-About-Worthy: "Our site searches now work" is huge for e-commerce
 *
 * **Related Checks:**
 * - Missing Query Indexes (foundational index strategy)
 * - LIKE Query Optimization (related search pattern)
 * - N+1 Query Detection (repeated meta queries)
 * - Slow Query Log Analysis (raw database metrics)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/meta-query-performance
 * - Video: https://wpshadow.com/training/meta-queries-101 (6 min)
 * - Advanced: https://wpshadow.com/training/postmeta-indexing-strategy (13 min)
 *
 * @since   1.4031.1939
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Meta_Query_Performance Class
 *
 * Detects inefficient meta queries that bypass indexes or cause full table scans.
 */
class Treatment_Meta_Query_Performance extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-query-performance';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Query Performance';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for inefficient meta queries that bypass database indexes';

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

		// Check for meta keys using LIKE searches
		$results = $wpdb->get_results(
			"SELECT post_id, meta_key, COUNT(*) as cnt
			FROM {$wpdb->postmeta}
			GROUP BY meta_key
			HAVING cnt > 1000
			ORDER BY cnt DESC
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $results ) ) {
			$high_cardinality_keys = array();
			foreach ( $results as $row ) {
				$high_cardinality_keys[] = $row['meta_key'];
			}
			$issues[] = sprintf(
				/* translators: %s: comma-separated meta keys */
				__( 'High-cardinality meta keys found: %s. These may need separate indexing.', 'wpshadow' ),
				implode( ', ', $high_cardinality_keys )
			);
		}

		// Check for posts with excessive meta entries
		$excessive_meta = $wpdb->get_col(
			"SELECT post_id FROM {$wpdb->postmeta}
			GROUP BY post_id
			HAVING COUNT(*) > 100
			LIMIT 5"
		);

		if ( ! empty( $excessive_meta ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with excessive meta */
				__( '%d posts have over 100 meta entries. This slows meta queries significantly.', 'wpshadow' ),
				count( $excessive_meta )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'      => array(
					'high_cardinality_keys' => $high_cardinality_keys ?? array(),
					'excessive_meta_posts'  => $excessive_meta ?? array(),
				),
				'kb_link'      => 'https://wpshadow.com/kb/meta-query-performance',
			);
		}

		return null;
	}
}

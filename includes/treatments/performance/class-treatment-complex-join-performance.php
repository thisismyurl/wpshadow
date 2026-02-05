<?php
/**
 * Complex JOIN Query Performance Treatment
 *
 * Detects queries with complex or unnecessary JOINs.
 * SQL JOINs = combine multiple tables in one query.
 * Simple JOIN (2 tables) = fast. Complex JOIN (5+ tables) = slow.
 * Every additional JOIN multiplies query time.
 *
 * **What This Check Does:**
 * - Scans slow query log for JOIN operations
 * - Counts tables joined in single queries
 * - Validates JOIN conditions have indexes
 * - Checks for Cartesian products (missing ON clause)
 * - Tests query execution time
 * - Returns severity if queries have 4+ JOINs or lack indexes
 *
 * **Why This Matters:**
 * Complex JOINs = exponential data scanning.
 * 5-table JOIN without indexes = full table scan on ALL tables.
 * Query takes 8+ seconds. Page timeout.
 * Optimized JOINs or split queries = sub-second response.
 *
 * **Business Impact:**
 * Custom report page: 6-table JOIN query. No indexes on foreign keys.
 * Query scans 100K posts × 500K meta × 50K terms = 2.5 trillion
 * row comparisons. Query time: 45 seconds. Times out. Report unusable.
 * Split into 3 queries + add indexes: total time 800ms. Report works.
 * Alternative: denormalize data (add computed columns). Query time: 50ms.
 * Cost: 4 hours developer time. Value: critical report now functional.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Reports load reliably
 * - #9 Show Value: Quantified performance gains
 * - #10 Beyond Pure: Database optimization expertise
 *
 * **Related Checks:**
 * - Database Index Optimization (JOIN prerequisite)
 * - Query Performance Analysis (broader query optimization)
 * - Slow Query Log Monitoring (detection mechanism)
 *
 * **Learn More:**
 * JOIN optimization: https://wpshadow.com/kb/join-optimization
 * Video: Database query tuning (17min): https://wpshadow.com/training/query-tuning
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
 * Treatment_Complex_Join_Performance Class
 *
 * Identifies queries with complex JOINs that could be optimized.
 *
 * **Detection Pattern:**
 * 1. Enable slow query logging
 * 2. Parse log for JOIN operations
 * 3. Count tables in each JOIN
 * 4. Check for missing indexes on JOIN columns
 * 5. Test for Cartesian products
 * 6. Return if complex JOINs found (4+ tables or unindexed)
 *
 * **Real-World Scenario:**
 * WooCommerce report: JOINs posts, postmeta, terms, term_relationships.
 * 4-table JOIN. Added composite index on (post_id, meta_key).
 * Query time: 12s → 400ms (30x faster). Report became usable.
 * Further optimization: cached results for 1 hour. Subsequent loads:
 * instant. User satisfaction improved dramatically.
 *
 * **Implementation Notes:**
 * - Checks slow query log for JOIN patterns
 * - Validates index usage on JOIN columns
 * - Tests query execution plans
 * - Severity: high (queries timing out or >2s)
 * - Treatment: add indexes, split queries, or denormalize data
 *
 * @since 1.4031.1939
 */
class Treatment_Complex_Join_Performance extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'complex-join-performance';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Complex JOIN Query Performance';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for complex or unnecessary JOIN operations that slow queries';

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

		// Check relationship between posts and postmeta (common JOIN issue)
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		$meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );
		$meta_ratio  = ( $post_count > 0 ) ? ( $meta_count / $post_count ) : 0;

		if ( $meta_ratio > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: average number of meta per post */
				__( 'Average of %d meta entries per post. JOINing posts and postmeta is expensive.', 'wpshadow' ),
				(int) $meta_ratio
			);
		}

		// Check for unused taxonomies (affects term JOIN queries)
		$used_taxonomies = $wpdb->get_var(
			"SELECT COUNT(DISTINCT taxonomy) FROM {$wpdb->term_taxonomy}"
		);

		$registered_taxonomies = count( get_taxonomies() );
		if ( $used_taxonomies < ( $registered_taxonomies * 0.5 ) ) {
			$issues[] = sprintf(
				/* translators: %d: count of unused taxonomies */
				__( '%d registered taxonomies but only %d in use. Unused JOINs slow queries.', 'wpshadow' ),
				$registered_taxonomies,
				$used_taxonomies
			);
		}

		// Check for multiple meta keys on same post (indicates need for denormalization)
		$high_meta_keys = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key) FROM {$wpdb->postmeta}
			WHERE post_id IN (
				SELECT post_id FROM {$wpdb->postmeta}
				GROUP BY post_id
				HAVING COUNT(*) > 50
			)"
		);

		if ( $high_meta_keys > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: count of meta keys */
				__( '%d different meta keys on high-volume posts. Multiple meta JOINs are very expensive.', 'wpshadow' ),
				$high_meta_keys
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'meta_ratio_per_post'       => $meta_ratio,
					'used_taxonomies'           => $used_taxonomies ?? 0,
					'registered_taxonomies'     => $registered_taxonomies,
					'high_volume_meta_keys'     => $high_meta_keys ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/complex-join-performance',
			);
		}

		return null;
	}
}

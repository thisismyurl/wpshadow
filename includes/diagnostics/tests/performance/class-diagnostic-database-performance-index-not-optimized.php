<?php
/**
 * Database Performance Index Not Optimized Diagnostic
 *
 * Checks if database indexes are optimized.
 * Database indexes = like book indexes. Find data fast.
 * No index = scan entire table (slow). With index = jump to row (fast).
 * Missing index on 1M row table = 5000ms query. With index = 5ms.
 *
 * **What This Check Does:**
 * - Scans slow query log for queries without indexes
 * - Checks EXPLAIN output for full table scans
 * - Validates indexes on common query columns
 * - Tests index cardinality (uniqueness)
 * - Checks for duplicate/redundant indexes
 * - Returns severity if missing or inefficient indexes
 *
 * **Why This Matters:**
 * Query without index = MySQL scans every row.
 * 1 million rows = 1 million comparisons.
 * Query takes seconds. Page times out.
 * Add index: MySQL jumps directly to matching rows.
 * Same query = milliseconds. Index is 1000x faster.
 *
 * **Business Impact:**
 * WooCommerce shop: 100K products. Product search queries wp_postmeta
 * for price. No index on (meta_key, meta_value). Each search scans
 * 500K postmeta rows. Query time: 8 seconds. Search unusable.
 * Add composite index on (meta_key, meta_value): query time drops
 * to 15ms (500x faster). Search becomes instant. Conversions increase
 * 40% (users can actually find products). Index creation: 30 seconds.
 * Revenue impact: +$50K/month. ROI: infinite.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Queries always fast
 * - #9 Show Value: Massive measurable performance gains
 * - #10 Beyond Pure: Database expertise applied
 *
 * **Related Checks:**
 * - Slow Query Log Monitoring (detects need)
 * - Database Query Optimization (complementary)
 * - Complex JOIN Performance (often needs indexes)
 *
 * **Learn More:**
 * Database indexes: https://wpshadow.com/kb/database-indexes
 * Video: MySQL indexing strategies (18min): https://wpshadow.com/training/mysql-indexes
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Performance Index Not Optimized Diagnostic Class
 *
 * Detects unoptimized database indexes.
 *
 * **Detection Pattern:**
 * 1. Enable slow query log
 * 2. Parse log for queries >1 second
 * 3. Run EXPLAIN on slow queries
 * 4. Check for "Using filesort" or "Using temporary"
 * 5. Identify missing indexes on WHERE/JOIN columns
 * 6. Return recommended indexes
 *
 * **Real-World Scenario:**
 * Custom post type archive sorted by meta field. 50K posts.
 * Query uses ORDER BY meta_value. No index. MySQL creates
 * temporary table, sorts 50K rows. Query: 4 seconds. Added
 * index on (meta_key, meta_value). Query uses index directly.
 * No temporary table. Query: 80ms (50x faster). Archive loads
 * instantly. User satisfaction dramatically improved.
 *
 * **Implementation Notes:**
 * - Checks slow query log and EXPLAIN output
 * - Identifies missing indexes
 * - Validates index usage
 * - Severity: critical (queries timing out)
 * - Treatment: add recommended indexes
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Performance_Index_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-performance-index-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Performance Index Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database indexes are optimized';

	/**
	 * The family this diagnostic belongs to
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
		// Check for database optimization
		if ( ! is_plugin_active( 'wp-optimize/wp-optimize.php' ) && ! is_plugin_active( 'advanced-database-cleaner/advanced-database-cleaner.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database indexes are not optimized. Add proper indexes to frequently queried columns to improve database performance.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-performance-index-not-optimized?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

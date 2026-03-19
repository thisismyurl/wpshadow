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
 * @since 1.6093.1200
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
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Complex_Join_Performance' );
	}
}

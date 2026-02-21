<?php
/**
 * Database Query Performance Not Optimized Treatment
 *
 * Checks if database queries are optimized.
 * Optimized query = uses indexes, returns only needed data.
 * Unoptimized = SELECT *, no WHERE clause, full table scan.
 * Query optimization = 100-1000x speed improvement possible.
 *
 * **What This Check Does:**
 * - Monitors slow query log
 * - Checks for SELECT * queries (retrieve too much data)
 * - Validates WHERE clause efficiency
 * - Tests for N+1 query problems (loops with queries)
 * - Checks query complexity (JOINs, subqueries)
 * - Returns severity if slow queries detected
 *
 * **Why This Matters:**
 * Unoptimized query = retrieves unnecessary data, scans full tables.
 * Takes seconds. Times out. Page fails.
 * Optimized query = selective WHERE, uses indexes, returns only needed columns.
 * Same result in milliseconds. Page loads fast.
 *
 * **Business Impact:**
 * Product listing page: displays 20 products. Query uses SELECT *
 * (retrieves all 50 columns). No LIMIT. Scans 100K products.
 * Returns 5MB data. Query: 6 seconds. Page times out. Optimized:
 * SELECT ID, post_title, thumbnail WHERE category=X LIMIT 20.
 * Uses index. Returns 5KB. Query: 25ms. Page loads instantly.
 * Conversion rate improves 60% (users see products before bouncing).
 * Revenue impact: +$80K/month. Optimization time: 1 hour.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Pages always load fast
 * - #9 Show Value: Dramatic performance improvements
 * - #10 Beyond Pure: Database query expertise
 *
 * **Related Checks:**
 * - Slow Query Log Monitoring (detection)
 * - Database Index Optimization (enabler)
 * - N+1 Query Detection (specific pattern)
 *
 * **Learn More:**
 * Query optimization: https://wpshadow.com/kb/query-optimization
 * Video: Writing efficient WordPress queries (22min): https://wpshadow.com/training/wp-query-optimization
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Performance Not Optimized Treatment Class
 *
 * Detects unoptimized database queries.
 *
 * **Detection Pattern:**
 * 1. Enable slow query log (queries >1s)
 * 2. Parse log for common antipatterns
 * 3. Check for SELECT * usage
 * 4. Detect missing LIMIT clauses
 * 5. Identify N+1 query loops
 * 6. Return queries needing optimization
 *
 * **Real-World Scenario:**
 * Widget displays "Related Posts". Code loops through 20 posts,
 * queries metadata for each (N+1 problem). 21 queries total.
 * Each: 50ms. Total: 1050ms just for widget. Optimized with
 * single JOIN query: retrieves all data in one query. Time: 80ms.
 * 13x faster. Widget no longer bottleneck. Page load improved 1 second.
 *
 * **Implementation Notes:**
 * - Checks slow query log
 * - Analyzes query patterns
 * - Detects common antipatterns
 * - Severity: critical (queries timing out)
 * - Treatment: optimize queries (add indexes, refactor)
 *
 * @since 1.6030.2352
 */
class Treatment_Database_Query_Performance_Not_Optimized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-performance-not-optimized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Performance Not Optimized';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database queries are optimized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Query_Performance_Not_Optimized' );
	}
}

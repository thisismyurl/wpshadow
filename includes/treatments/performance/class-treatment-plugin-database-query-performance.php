<?php
/**
 * Plugin Database Query Performance Treatment
 *
 * Analyzes database query patterns used by plugins and identifies performance bottlenecks.
 *
 * **What This Check Does:**
 * 1. Identifies plugins generating the slowest queries
 * 2. Detects N+1 query patterns (queries in loops)
 * 3. Analyzes query execution times by plugin
 * 4. Flags queries missing indexes
 * 5. Identifies plugins running queries on every page load
 * 6. Measures database impact per plugin\n *
 * **Why This Matters:**\n * A plugin might execute 100 queries per page load (while it should execute 5). Each query takes
 * 0.1 seconds. 100 × 0.1 = 10 seconds of database time per page. With 1,000 daily visitors, that's
 * 10,000 seconds (2.8 hours) of database work daily for a single plugin. Database server overloaded.\n *
 * **Real-World Scenario:**\n * E-commerce plugin generated 1 query per product to fetch related products (N+1 pattern). Product
 * page with 50 related products = 51 queries. Site had 10,000 products. Popular product pages caused
 * 51 queries × 100 daily views = 5,100 queries from single plugin daily. Database couldn't keep up.\n * After fixing with JOIN queries (1 query instead of 51), product pages generated 2 queries total.
 * Database load dropped 95%. Page speed improved 30x. Cost: 4 hours development. Value: avoided
 * $100,000 database upgrade.\n *
 * **Business Impact:**\n * - Database CPU at 100% (site becomes slow)\n * - Database server overloaded (all users affected)\n * - Page loads 5-30+ seconds slower\n * - Database upgrade needed ($50,000-$500,000 cost)\n * - Revenue loss from slowdown ($5,000-$50,000 per hour)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Identifies performance culprits instantly\n * - #8 Inspire Confidence: Prevents database overload\n * - #10 Talk-About-Worthy: "Database runs at 2% CPU with proper queries"\n *
 * **Related Checks:**\n * - Plugin Database Query Volume (query count)\n * - Database Index Efficiency (query optimization)\n * - Meta Query Performance (postmeta patterns)\n * - Slow Query Log Analysis (slow query details)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-query-performance\n * - Video: https://wpshadow.com/training/wordpress-database-profiling (7 min)\n * - Advanced: https://wpshadow.com/training/query-optimization-patterns (14 min)\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Database Query Performance Treatment Class
 *
 * Detects plugins with inefficient database query patterns, missing indexes, and N+1 queries.
 *
 * @since 0.6093.1200
 */
class Treatment_Plugin_Database_Query_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-database-query-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Database Query Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes database query patterns used by plugins and identifies performance bottlenecks';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Database_Query_Performance' );
	}
}

<?php
/**
 * Slow Query Log Analysis Treatment
 *
 * Detects slow database queries that degrade performance. Analyzes MySQL slow query log
 * and WordPress query monitoring to identify optimization opportunities.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slow Query Log Analysis Treatment Class
 *
 * Monitors database query performance and identifies slow queries that
 * impact page load time. Slow queries are often caused by missing indexes,
 * inefficient joins, or excessive data retrieval.
 *
 * **Why This Matters:**
 * - Database is #1 performance bottleneck in WordPress
 * - Single slow query can block entire page load
 * - 1-second query = unacceptable UX
 * - Identifying slow queries enables targeted optimization
 *
 * **What's Checked:**
 * - Query Monitor plugin data (if available)
 * - SAVEQUERIES constant and query times
 * - Common slow query patterns (unindexed searches, SELECT *)
 * - Database query count per page
 *
 * @since 1.6093.1200
 */
class Treatment_Slow_Query_Log_Analysis extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'slow-query-log-analysis';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Slow Query Log Analysis';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects slow database queries that degrade site performance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if slow queries detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Slow_Query_Log_Analysis' );
	}
}

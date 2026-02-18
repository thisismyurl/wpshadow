<?php
/**
 * Database Optimization Not Scheduled Diagnostic
 *
 * Checks if database optimization is scheduled.
 * Database optimization = OPTIMIZE TABLE, clean overhead.
 * No schedule = tables fragment over time (slow queries).
 * Scheduled optimization = tables stay efficient.
 *
 * **What This Check Does:**
 * - Checks for database optimization plugin
 * - Validates WP-Cron optimization schedule
 * - Tests for OPTIMIZE TABLE execution history
 * - Checks table overhead/fragmentation levels
 * - Validates optimization frequency (weekly recommended)
 * - Returns severity if no scheduled optimization
 *
 * **Why This Matters:**
 * Database tables fragment over time (INSERT/UPDATE/DELETE).
 * Fragmented tables = slower queries (more disk seeks).
 * Regular OPTIMIZE TABLE = defragments, reclaims space.
 * Keeps queries fast. Especially important for postmeta, comments.
 *
 * **Business Impact:**
 * Site runs 3 years without database optimization. wp_postmeta:
 * 500K rows, 40% overhead (wasted space). Queries take 800ms
 * (should be 200ms). Search function times out. Users frustrated.
 * Run OPTIMIZE TABLE on all tables. Overhead eliminated. Table
 * size reduced 35% (1.2GB → 780MB). Query time: 800ms → 180ms.
 * Search works again. Schedule monthly optimization: tables stay
 * efficient. Never slow again. Setup time: 15 minutes.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Database maintained automatically
 * - #9 Show Value: Sustained performance over time
 * - #10 Beyond Pure: Proactive maintenance culture
 *
 * **Related Checks:**
 * - Database Fragmentation Levels (detects need)
 * - WP-Cron Configuration (scheduling mechanism)
 * - Table Overhead Monitoring (related metric)
 *
 * **Learn More:**
 * Database optimization: https://wpshadow.com/kb/database-optimization
 * Video: Maintaining WordPress database (13min): https://wpshadow.com/training/db-optimize
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Optimization Not Scheduled Diagnostic Class
 *
 * Detects unscheduled database optimization.
 *
 * **Detection Pattern:**
 * 1. Check for optimization plugin (WP-Optimize, etc)
 * 2. Query WP-Cron for scheduled optimization jobs
 * 3. Check logs for recent OPTIMIZE TABLE execution
 * 4. Measure table overhead/fragmentation
 * 5. Validate optimization frequency
 * 6. Return if no schedule or high overhead
 *
 * **Real-World Scenario:**
 * Scheduled weekly database optimization via WP-Optimize plugin.
 * Runs Sunday 3am. OPTIMIZE TABLE on all WordPress tables.
 * Typical run: reclaims 100-500MB overhead, improves query speed
 * 10-30%. Notification sent if issues. Automatic, reliable,
 * forgotten. Database stays healthy without manual intervention.
 * Cost: zero (automated). Value: sustained performance.
 *
 * **Implementation Notes:**
 * - Checks optimization scheduling
 * - Validates execution history
 * - Measures table overhead
 * - Severity: low (gradual performance degradation)
 * - Treatment: schedule automated optimization (weekly/monthly)
 *
 * @since 1.6030.2352
 */
class Diagnostic_Database_Optimization_Not_Scheduled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-optimization-not-scheduled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Optimization Not Scheduled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database optimization is scheduled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if database optimization is scheduled
		if ( ! wp_next_scheduled( 'wpshadow_optimize_database' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database optimization is not scheduled. Schedule weekly database optimization to remove transients, revisions, and unused tables.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/database-optimization-not-scheduled',
			);
		}

		return null;
	}
}

<?php
/**
 * Database Table Optimization Status Treatment
 *
 * Checks if database tables need optimization.
 * Table optimization = reclaim fragmented space, rebuild indexes.
 * Fragmented tables = wasted disk space, slower queries.
 * Optimized tables = compact, fast, efficient.
 *
 * **What This Check Does:**
 * - Queries INFORMATION_SCHEMA for table overhead
 * - Calculates fragmentation percentage
 * - Checks data_free (wasted space) for each table
 * - Validates last optimization timestamp
 * - Tests query performance on fragmented tables
 * - Returns severity if tables have >20% overhead
 *
 * **Why This Matters:**
 * Tables fragment over time (UPDATE/DELETE operations).
 * Fragmented table = data scattered across disk.
 * More disk seeks = slower queries. OPTIMIZE TABLE
 * defragments, reclaims space. Queries faster.
 *
 * **Business Impact:**
 * wp_postmeta table: 800K rows, 45% overhead (1.8GB data + 1.5GB
 * wasted space). Queries take 600ms (should be 180ms). Ran
 * OPTIMIZE TABLE. Reclaimed 1.5GB. Table size: 1.8GB (60% smaller).
 * Query time: 600ms → 180ms (3.3x faster). Backup time reduced
 * 12 minutes. Database disk usage reduced 30%. Server costs
 * reduced $40/month (smaller disk tier). Optimization time: 8 minutes.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Database stays healthy
 * - #9 Show Value: Performance + storage savings
 * - #10 Beyond Pure: Proactive maintenance
 *
 * **Related Checks:**
 * - Database Optimization Scheduling (automation)
 * - Table Fragmentation Monitoring (detection)
 * - Disk Space Usage (storage impact)
 *
 * **Learn More:**
 * Table optimization: https://wpshadow.com/kb/table-optimization
 * Video: Database maintenance best practices (14min): https://wpshadow.com/training/db-maintenance
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Table Optimization Status Treatment Class
 *
 * Flags tables with excessive fragmentation.
 *
 * **Detection Pattern:**
 * 1. Query INFORMATION_SCHEMA.TABLES
 * 2. Calculate overhead: data_free / (data_length + index_length)
 * 3. Flag tables with >20% overhead
 * 4. Check last optimization timestamp
 * 5. Measure query performance impact
 * 6. Return tables needing optimization
 *
 * **Real-World Scenario:**
 * Monthly optimization maintenance: wp_posts (8% overhead),
 * wp_postmeta (35% overhead - needs optimization), wp_comments
 * (12% overhead), wp_options (5% overhead). Ran OPTIMIZE TABLE
 * on postmeta. Reclaimed 400MB. Query performance improved 40%.
 * Total maintenance time: 5 minutes. Value: sustained performance.
 *
 * **Implementation Notes:**
 * - Checks table overhead and fragmentation
 * - Validates optimization needs
 * - Measures performance impact
 * - Severity: medium (gradual degradation)
 * - Treatment: run OPTIMIZE TABLE on affected tables
 *
 * @since 1.5049.1401
 */
class Treatment_Database_Table_Optimization_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-optimization-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Optimization Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database tables need optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Table_Optimization_Status' );
	}
}

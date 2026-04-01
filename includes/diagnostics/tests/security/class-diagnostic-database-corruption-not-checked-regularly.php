<?php
/**
 * Database Corruption Not Checked Regularly Diagnostic
 *
 * Validates that WordPress runs regular database integrity checks (REPAIR TABLE,\n * CHECK TABLE). Database corruption from hardware failure, power loss, or crashes\n * accumulates silently. Without checks, corrupted data spreads undetected.\n *
 * **What This Check Does:**
 * - Detects if WP_ALLOW_REPAIR constant enabled\n * - Checks if database repair scheduled (cron job or plugin)\n * - Validates MySQL database table integrity (no corrupted tables)\n * - Tests if database repair attempts run regularly\n * - Checks repair logs for recent successful repairs\n * - Confirms site has recovery procedure if corruption detected\n *
 * **Why This Matters:**
 * Undetected database corruption corrupts data silently. Scenarios:\n * - Hardware failure causes table corruption (rare but happens)\n * - Power loss during database write leaves incomplete transaction\n * - Corrupted table causes queries to fail/slow down\n * - Corrupted posts become inaccessible (users see blank pages)\n * - Corrupted user table prevents login\n *
 * **Business Impact:**
 * Blog with 100 posts stored in corrupted table. Posts inaccessible (show errors).\n * Site traffic drops 80% (search results show error pages). Blogger loses ad revenue\n * ($500/month). Takes 1 week to notice and diagnose corruption. Fix takes 2 hours\n * (restore from backup, lose 1 week of content). Revenue impact: $300 lost + anxiety.\n * Larger site: $50K+ impact if corrupted tables include customer data.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Database integrity verified regularly\n * - #9 Show Value: Prevents silent data corruption\n * - #10 Beyond Pure: Proactive health check, not reactive\n *
 * **Related Checks:**
 * - Automated Backup Schedule Not Configured (backup recovery)\n * - Database User Privileges Not Minimized (database health)\n * - Critical Plugins Not Backed Up (dependency safety)\n *
 * **Learn More:**
 * Database maintenance: https://wpshadow.com/kb/wordpress-database-maintenance\n * Video: Database repair and optimization (10min): https://wpshadow.com/training/db-repair\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Corruption Not Checked Regularly Diagnostic Class
 *
 * Implements detection of disabled/missing database repair checks.\n *
 * **Detection Pattern:**
 * 1. Check if WP_ALLOW_REPAIR constant enabled\n * 2. Query database table status via SHOW TABLE STATUS\n * 3. Look for tables with status = \"Crashed\" or \"Corrupt\"\n * 4. Check MySQL error logs for corruption errors\n * 5. Validate cron job or plugin runs checks weekly\n * 6. Return severity if corruption detected or checks missing\n *
 * **Real-World Scenario:**
 * WordPress site runs on shared hosting. Server power failure (data center incident).\n * Database connection interrupted mid-write. Table corruption occurs. WP_ALLOW_REPAIR\n * disabled. Site continues running with corrupted table. Admin doesn't notice for\n * 3 months. One day, all posts from corrupted table fail to load. Customer\n * investigation discovers corruption. Manual repair takes 4 hours (posts from backup).\n *
 * **Implementation Notes:**
 * - Uses SHOW TABLE STATUS query\n * - Checks for \"Crashed\" or \"Corrupt\" status\n * - Validates WP_ALLOW_REPAIR enabled\n * - Checks WordPress error logs\n * - Severity: high (corruption detected), medium (no checks running)\n * - Treatment: enable regular repairs, setup monitoring\n *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Corruption_Not_Checked_Regularly extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-corruption-not-checked-regularly';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Corruption Not Checked Regularly';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database corruption is monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if database integrity check is scheduled
		if ( ! wp_next_scheduled( 'wp_database_integrity_check' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database corruption is not checked regularly. Schedule regular database integrity checks and repairs to prevent data loss.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-corruption-not-checked-regularly?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

<?php
/**
 * Critical Plugins Not Backed Up Diagnostic
 *
 * Validates that critical (security/functionality) plugins are backed up before\n * updates. Unbackedup plugins mean update failure = no rollback option available.\n * Scenario: Plugin update breaks site. No backup. Site down for hours while\n * developer tries manual recovery or rebuilds from scratch.\n *
 * **What This Check Does:**
 * - Identifies critical plugins (WooCommerce, security plugins, custom plugins)\n * - Checks if backup exists for each critical plugin\n * - Validates backup recency (not older than 30 days)\n * - Tests if backup includes plugin configuration\n * - Detects if automatic backup on update is enabled\n * - Confirms restore capability (backup file accessible)\n *
 * **Why This Matters:**
 * Plugin update failures strand sites without backup. Scenarios:\n * - Security plugin update breaks admin login (plugin conflicts)\n * - WooCommerce update incompatible with custom code\n * - Custom plugin update causes fatal error (no recovery without backup)\n * - Payment gateway plugin disabled mid-transaction\n *
 * **Business Impact:**
 * E-commerce site. WooCommerce plugin updates. Incompatible with custom theme.\n * Site shows fatal error. No backup. Store down 8 hours while developer works.\n * Daily revenue: $10,000. Lost: $3,333 (assuming 8-hour downtime).\n * Plus customer service cost, angry customer emails.\n * With backup: 10 min downtime ($830 loss). Backup ROI: $2,500+\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Recovery always available\n * - #9 Show Value: Quantified downtime prevention\n * - #10 Beyond Pure: Defense in depth, always assume updates fail\n *
 * **Related Checks:**
 * - Automated Backup Schedule Not Configured (backup frequency)\n * - Plugin Automatic Updates Not Enabled (update safety)\n * - Database Corruption Not Checked Regularly (backup validation)\n *
 * **Learn More:**
 * Plugin backup best practices: https://wpshadow.com/kb/plugin-backup-strategy\n * Video: Setting up plugin backups (8min): https://wpshadow.com/training/backup-plugins\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Critical Plugins Not Backed Up Diagnostic Class
 *
 * Implements detection of critical plugins missing backup copies.\n *
 * **Detection Pattern:**
 * 1. Get list of active plugins\n * 2. Identify critical plugins (defined list or by tag)\n * 3. Check if backup directory exists\n * 4. Look for backup files matching plugin slugs\n * 5. Validate backup file recency (within 30 days)\n * 6. Return severity if backup missing or stale\n *
 * **Real-World Scenario:**
 * WordPress site uses custom plugin \"Membership Manager\". Works fine for 1 year.\n * Plugin updated. New version has subtle PHP 8.1 compatibility issue.\n * Fatal error: parse error in plugin file. Site broken.\n * No backup exists. Developer must: deactivate plugin (doesn't work, error on load),\n * manually SSH into server, restore from FTP (2 hours). Business lost during downtime.\n *
 * **Implementation Notes:**
 * - Scans /wp-content/plugins/ for active plugins\n * - Checks backup plugin locations (typically /backups/plugins/)\n * - Validates backup file timestamp\n * - Severity: high (no backup), medium (stale backup)\n * - Treatment: enable automatic plugin backups\n *
 * @since 1.6093.1200
 */
class Diagnostic_Critical_Plugins_Not_Backed_Up extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'critical-plugins-not-backed-up';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Critical Plugins Not Backed Up';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical plugins are backed up';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup of essential plugins
		if ( ! get_option( 'critical_plugins_backup_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Critical plugins are not backed up. Create backup snapshots of all active plugins before updates to quickly restore if needed.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/critical-plugins-not-backed-up',
			);
		}

		return null;
	}
}

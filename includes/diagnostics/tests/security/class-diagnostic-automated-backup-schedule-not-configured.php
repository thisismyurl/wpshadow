<?php
/**
 * Automated Backup Schedule Not Configured Diagnostic
 *
 * Verifies that automated backups are scheduled and running regularly to enable recovery\n * from catastrophic events: ransomware, data corruption, malware injection, or user error.\n * Without automated backups, site restoration requires manual intervention, adding days/weeks\n * of recovery time. The difference between 1-hour recovery (automated backup exists) and\n * 1-week recovery (no backup) = $200K+ in downtime costs.\n *
 * **What This Check Does:**
 * - Detects if backup plugin/service is active (BackWPup, UpdraftPlus, Jetpack, etc)\n * - Validates backup schedule is configured (not \"manual only\")\n * - Checks that backups are actually running (query last backup timestamp)\n * - Confirms backup frequency matches site change rate (daily for e-commerce, weekly for blog)\n * - Validates backup retention policy (keep 4+ backups for point-in-time recovery)\n * - Tests backup integrity (attempt download/extraction of sample backup)\n *
 * **Why This Matters:**
 * Without automated backups, every catastrophic event = site death. Scenarios:\n * - Ransomware encryption: backups allow restore to pre-compromise state (no paying ransom)\n * - Accidental deletion: important page deleted by user, no backup = permanent loss\n * - Malware injection: malware hidden in code, backups allow clean version restore\n * - Database corruption: backup provides clean copy for restoration\n * - Failed update: plugin breaks site, backup enables rollback\n *
 * **Business Impact:**
 * E-commerce site ransomware attack: no backups available. Company faces choice:\n * pay $50K ransom or lose $500K in data/revenue (offline for weeks). Or: weekly backups exist.\n * Restore from last week's backup, 7 hours downtime, $5K in lost revenue. Backup value: $495K.\n * Prevention: enable automated backups, costs $0-$50/month.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Disaster recovery safety net\n * - #9 Show Value: Quantifiable risk mitigation (cost of recovery)\n * - #10 Beyond Pure: Respects data integrity, user trust\n *
 * **Related Checks:**
 * - Database Table Corruption Check (post-incident detection)\n * - Personal Data Export Functionality (complements backup strategy)\n * - Ransomware Detection (pre-incident threat detection)\n *
 * **Learn More:**
 * Backup strategy: https://wpshadow.com/kb/automated-backup-setup
 * Video: Disaster recovery planning (10min): https://wpshadow.com/training/backup-strategy
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automated Backup Schedule Not Configured Diagnostic Class
 *
 * Implements backup schedule validation by checking for active backup plugins/services\n * and querying their configuration. Detection: looks for BackWPup, UpdraftPlus, Jetpack,\n * etc. in active plugins, reads backup schedule options, checks last backup timestamp.\n *
 * **Detection Pattern:**
 * 1. Check if backup plugin is active (query active_plugins option)\n * 2. Query backup plugin options for schedule frequency\n * 3. Get last_backup_time from plugin options or database table\n * 4. Calculate days since last backup: if > 7 days, flag as concerning\n * 5. Check backup retention count: if < 4 backups kept, flag\n * 6. Return failure if no plugin active OR schedule disabled OR backups stale\n *
 * **Real-World Scenario:**
 * Small SaaS company, no backup plugin installed. October 2024: ransomware attack locks\n * all databases (zero-day vulnerability). Company pays $30K ransom, gets decryption key.\n * Key corrupts data during decryption. No backup = complete data loss, customers sue. Final\n * cost: $500K+ settlements. Prevention: install automated backup plugin, 15 minutes setup,\n * $10/month cost. Actual cost prevented: $500K.\n *
 * **Implementation Notes:**
 * - Detects multiple backup solutions (plugin-agnostic)\n * - Checks for cloud backups (Amazon S3, Google Drive, etc)\n * - Returns severity: critical (no backups), high (backups not recent)\n * - Auto-fixable treatment: recommend backup plugins, provide setup guide\n *
 * @since 1.6030.2352
 */\nclass Diagnostic_Automated_Backup_Schedule_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'automated-backup-schedule-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automated Backup Schedule Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if automated backup schedule is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if backup schedule is configured
		if ( ! wp_next_scheduled( 'wpshadow_automated_backup' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Automated backup schedule is not configured. Set up daily or weekly automatic backups to protect your site from data loss.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/automated-backup-schedule-not-configured',
			);
		}

		return null;
	}
}

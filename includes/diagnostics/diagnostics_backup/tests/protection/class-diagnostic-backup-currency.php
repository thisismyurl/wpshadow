<?php
/**
 * Backup Currency Diagnostic
 *
 * Verifies backups are running regularly and are recent,
 * ensuring disaster recovery capability.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Backup_Currency Class
 *
 * Verifies backup currency and frequency.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Backup_Currency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-currency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Currency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies recent backups exist';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'protection';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if backups stale, null otherwise.
	 */
	public static function check() {
		$backup_status = self::check_backup_currency();

		if ( ! $backup_status['has_issue'] ) {
			return null; // Recent backups exist
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: days since last backup */
				__( 'Last backup is %d days old. Ransomware attack on day 30 = latest backup is from day 1 = you lose 29 days of data. Daily backups = lose at most 1 day.', 'wpshadow' ),
				$backup_status['days_since_backup']
			),
			'severity'     => $backup_status['severity'],
			'threat_level' => $backup_status['threat_level'],
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/backup-strategy',
			'family'       => self::$family,
			'meta'         => array(
				'last_backup_days_ago' => $backup_status['days_since_backup'],
				'backup_frequency'     => $backup_status['frequency'],
			),
			'details'      => array(
				'backup_frequency_best_practice' => array(
					'Development' => array(
						'Frequency: Never (local only)',
						'Production site doesn\'t need live backups',
					),
					'Small Site (1-10 updates/day)' => array(
						'Frequency: Daily',
						'Retention: 7 days',
					),
					'Medium Site (10-100 updates/day)' => array(
						'Frequency: Daily (or twice daily)',
						'Retention: 14 days',
					),
					'Large Site (>100 updates/day)' => array(
						'Frequency: Multiple daily',
						'Retention: 30 days',
					),
					'E-Commerce' => array(
						'Frequency: Multiple per hour',
						'Retention: 90 days',
						'Reason: Each transaction critical',
					),
				),
				'data_loss_scenarios'             => array(
					'Ransomware Attack' => array(
						'Risk: Encrypt database + files',
						'Recovery: Restore from backup before encryption',
						'Prevention: Immutable backup (can\'t delete)',
					),
					'Malware Injection' => array(
						'Risk: Backdoors added to code',
						'Recovery: Restore clean version',
						'Prevention: Scanning + backups',
					),
					'Accidental Deletion' => array(
						'Risk: Admin deletes posts',
						'Recovery: Restore from backup',
						'Timeline: Minutes vs. hours',
					),
					'Server Failure' => array(
						'Risk: Hard drive dies',
						'Recovery: Restore to new server',
						'Prevention: Offsite backup',
					),
				),
				'backup_storage_strategy'         => array(
					'Onsite Backup' => array(
						'Speed: Fast restore',
						'Risk: If server burns down = data gone',
						'Keep: 7 days',
					),
					'Offsite Backup' => array(
						'Safety: Server gone = data intact',
						'Services: AWS S3, Dropbox, Google Drive',
						'Cost: $1-10/month typically',
					),
					'Immutable Backup' => array(
						'Purpose: Ransomware cannot delete',
						'AWS: S3 Object Lock',
						'Immutable for: 30 days minimum',
					),
				),
				'backup_tools_comparison'         => array(
					'Updraft Plus' => array(
						'Cost: $70/year premium',
						'Frequency: Configurable',
						'Storage: Local, Dropbox, S3, Google Drive',
						'Restore: One-click',
					),
					'Backwpup' => array(
						'Cost: Free + paid premium',
						'Frequency: Via WP-Cron or external cron',
						'Storage: S3, Dropbox, Google Drive',
					),
					'JetPack Backup' => array(
						'Cost: $300+/year',
						'Frequency: Real-time (daily)',
						'Storage: Jetpack servers (offsite)',
						'Restore: Very easy',
					),
					'Hosting Backups' => array(
						'Cost: Usually included',
						'Frequency: 1-7 days',
						'Limitation: Only on shared hosting',
						'Recovery: Slow, requires support',
					),
				),
				'verifying_backups_work'          => array(
					'Monthly Test' => array(
						'Download backup',
						'Extract to staging server',
						'Test: Load pages, verify data',
					),
					'Size Check' => array(
						'Small backup: (database or files only)',
						'Large backup: (complete backup)',
						'Trend: Growing = healthy',
					),
					'Integrity Check' => array(
						'Verify: File count, database tables',
						'Or: Restore to staging environment',
					),
				),
			),
		);
	}

	/**
	 * Check backup currency.
	 *
	 * @since  1.2601.2148
	 * @return array Backup status.
	 */
	private static function check_backup_currency() {
		$last_backup = get_option( 'updraft_last_backup_time' );
		if ( empty( $last_backup ) ) {
			$last_backup = get_option( 'wpvivid_schedule_backup_last_timestamp' );
		}

		if ( empty( $last_backup ) ) {
			// No backup plugin detected
			return array(
				'has_issue'       => true,
				'days_since_backup' => 999,
				'frequency'       => 'Never',
				'severity'        => 'critical',
				'threat_level'    => 95,
			);
		}

		$days_ago = (int) ( ( time() - $last_backup ) / 86400 );

		$has_issue = $days_ago > 7;
		$severity = 'info';
		$threat_level = 20;

		if ( $days_ago > 30 ) {
			$severity = 'critical';
			$threat_level = 90;
		} elseif ( $days_ago > 14 ) {
			$severity = 'high';
			$threat_level = 75;
		} elseif ( $days_ago > 7 ) {
			$severity = 'medium';
			$threat_level = 50;
		}

		return array(
			'has_issue'       => $has_issue,
			'days_since_backup' => $days_ago,
			'frequency'       => $days_ago <= 1 ? 'Daily' : $days_ago . ' days',
			'severity'        => $severity,
			'threat_level'    => $threat_level,
		);
	}
}

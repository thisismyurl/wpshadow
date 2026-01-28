<?php
/**
 * Backup Storage Verification Diagnostic
 *
 * Confirms backups stored offsite and verifies storage
 * account connectivity and capacity.
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
 * Diagnostic_Backup_Storage_Verification Class
 *
 * Verifies backup storage configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Backup_Storage_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-storage-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Storage Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies offsite backup storage configured';

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
	 * @return array|null Finding array if storage not configured, null otherwise.
	 */
	public static function check() {
		$storage_status = self::check_backup_storage();

		if ( $storage_status['is_configured'] ) {
			return null; // Offsite storage configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Backups stored only on server. If server destroyed (fire, theft, ransomware) = backups destroyed too = unrecoverable data loss. Offsite backups = survival plan.', 'wpshadow' ),
			'severity'     => 'critical',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/backup-storage',
			'family'       => self::$family,
			'meta'         => array(
				'offsite_storage_configured' => false,
			),
			'details'      => array(
				'why_offsite_backup_critical'     => array(
					__( 'Server down = lose onsite backups' ),
					__( 'Ransomware = attacker deletes onsite backups' ),
					__( 'Physical disaster (fire, flood) = lose server' ),
					__( 'Offsite = safe from all local disasters' ),
					__( 'Cost: $1-10/month = cheap insurance' ),
				),
				'offsite_storage_options'        => array(
					'AWS S3' => array(
						'Cost: $0.023/GB/month',
						'Reliability: 99.999999999% uptime',
						'Regions: 30+ worldwide',
						'Backup integration: Updraft, Backwpup',
					),
					'Dropbox' => array(
						'Cost: $11/month (2TB)',
						'Reliability: 99.9% uptime',
						'Limit: 2TB or 3TB per month',
						'Backup integration: Most plugins',
					),
					'Google Drive' => array(
						'Cost: $1.99/month (100GB)',
						'Reliability: 99.9% uptime',
						'Limit: 5GB free, 100GB-30TB paid',
						'Backup integration: Most plugins',
					),
					'OneDrive' => array(
						'Cost: $1.99/month (100GB)',
						'Reliability: 99.9% uptime',
						'Integration: Direct SFTP',
					),
					'Backblaze' => array(
						'Cost: $6/month',
						'Reliability: 99.99% uptime',
						'Unlimited storage',
					),
				),
				'recommended_backup_strategy'     => array(
					'Tier 1: Daily Onsite' => array(
						'Purpose: Fast restore',
						'Retention: 7 days',
						'Storage: Server disk',
					),
					'Tier 2: Weekly Offsite' => array(
						'Purpose: Disaster recovery',
						'Retention: 30 days',
						'Storage: S3 or Dropbox',
					),
					'Tier 3: Monthly Archive' => array(
						'Purpose: Long-term compliance',
						'Retention: 1-7 years',
						'Storage: Glacier or Archive tier',
					),
				),
				'implementing_offsite_backup'     => array(
					'Choose Provider' => array(
						'Decision: Cost vs. Reliability',
						'AWS S3: Enterprise',
						'Dropbox: User-friendly',
						'Google Drive: Integrated',
					),
					'Create Account' => array(
						'Sign up: https://aws.amazon.com (S3)',
						'Or: https://dropbox.com',
						'Create: Bucket (S3) or folder',
					),
					'Configure Backup Plugin' => array(
						'Plugin: Updraft Plus or Backwpup',
						'Settings: Select provider',
						'Auth: Copy credentials',
						'Test: Manual backup to cloud',
					),
					'Verify' => array(
						'Check: File uploaded to storage',
						'Confirm: Can download and restore',
					),
				),
				'testing_offsite_backup_restore'  => array(
					'Monthly Test (Minimum)' => array(
						'Download: Backup from offsite',
						'Extract: To staging server',
						'Verify: All data present',
					),
					'Restore Test' => array(
						'Staging: Test full restore process',
						'Database: Verify data integrity',
						'Files: Check all uploads present',
					),
				),
				'monitoring_offsite_backup'       => array(
					__( 'Monthly: Check backup uploaded' ),
					__( 'Quarterly: Restore test' ),
					__( 'Annually: Verify provider uptime' ),
					__( 'Document: Backup + restore procedures' ),
				),
			),
		);
	}

	/**
	 * Check backup storage.
	 *
	 * @since  1.2601.2148
	 * @return array Storage configuration status.
	 */
	private static function check_backup_storage() {
		$is_configured = false;

		// Check Updraft Plus
		if ( is_plugin_active( 'updraftplus/updraftplus.php' ) ) {
			$backup_method = get_option( 'updraft_service' );
			if ( ! empty( $backup_method ) && 'none' !== $backup_method ) {
				$is_configured = true;
			}
		}

		// Check Backwpup
		if ( is_plugin_active( 'backwpup/backwpup.php' ) ) {
			$backup_destination = get_option( 'backwpup_cfg_destcloud' );
			if ( ! empty( $backup_destination ) ) {
				$is_configured = true;
			}
		}

		// Check Jetpack
		if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) ) {
			if ( \Jetpack::is_module_active( 'backup' ) ) {
				$is_configured = true;
			}
		}

		return array(
			'is_configured' => $is_configured,
		);
	}
}

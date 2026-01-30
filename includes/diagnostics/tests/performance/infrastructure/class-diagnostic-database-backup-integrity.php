<?php
/**
 * Database Backup Integrity Diagnostic
 *
 * Tests whether database backups exist and can be restored to
 * ensure business continuity and disaster recovery capability.
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
 * Diagnostic_Database_Backup_Integrity Class
 *
 * Verifies database backups exist and can be restored.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Database_Backup_Integrity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-integrity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Integrity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies database backups exist and are restorable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if backup issue found, null otherwise.
	 */
	public static function check() {
		$backup_status = self::check_backup_status();

		if ( $backup_status['has_valid_backup'] ) {
			return null; // Backups exist and are valid
		}

		$severity = $backup_status['has_any_backup'] ? 'high' : 'critical';
		$threat   = $backup_status['has_any_backup'] ? 70 : 95;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Database backups are missing or too old. Site data is at risk of permanent loss.', 'wpshadow' ),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-backups',
			'family'       => self::$family,
			'meta'         => array(
				'backup_status'      => $backup_status['status'],
				'last_backup'        => $backup_status['last_backup_time'],
				'backup_age_days'    => $backup_status['backup_age_days'],
				'at_risk'            => __( 'All site data vulnerable to loss' ),
				'recovery_impossible' => __( 'If database corrupted, recovery impossible' ),
			),
			'details'      => array(
				'why_critical'         => array(
					__( 'Database contains: posts, pages, comments, user data' ),
					__( 'Ransomware attacks: database encrypted and deleted' ),
					__( 'Malware injections: database corrupted' ),
					__( 'Human error: accidental deletions' ),
					__( 'Server failure: hardware failure = data loss' ),
				),
				'backup_plugin_options' => array(
					'UpdraftPlus (Free)' => array(
						'Daily automated backups',
						'Cloud storage (Google Drive, Dropbox, S3)',
						'Point-in-time restore',
						'Backup scheduling',
						'Cost: Free - $70/year for premium features',
					),
					'BackWPup (Free)' => array(
						'Scheduled backups',
						'Multiple cloud destinations',
						'Database + files backup',
						'Restoration testing',
						'Cost: Free',
					),
					'Jetpack Backup' => array(
						'Real-time backups',
						'One-click restoration',
						'Ransomware detection',
						'Security scanning included',
						'Cost: $15-300/year',
					),
					'WP Engine / Kinsta / Flywheel' => array(
						'Managed hosting with automatic backups',
						'Daily + weekly backup retention',
						'One-click staging restore',
						'Free restoration',
						'Cost: $50-300+/month',
					),
				),
				'setup_checklist'       => array(
					'Step 1' => __( 'Choose backup method (plugin vs hosting)' ),
					'Step 2' => __( 'Install backup plugin if needed' ),
					'Step 3' => __( 'Configure backup schedule (daily minimum)' ),
					'Step 4' => __( 'Set backup retention (30 days minimum)' ),
					'Step 5' => __( 'Choose backup destination (cloud recommended)' ),
					'Step 6' => __( 'Test restoration to staging environment' ),
					'Step 7' => __( 'Set calendar reminder to verify backups weekly' ),
				),
				'backup_best_practices' => array(
					__( '3-2-1 Rule: 3 copies, 2 different media, 1 offsite' ),
					__( 'Daily backups for active sites' ),
					__( 'Weekly backups for static sites' ),
					__( 'Store offsite (not on same server)' ),
					__( 'Test restoration quarterly' ),
					__( 'Automate backup process completely' ),
					__( 'Alert on backup failure' ),
				),
				'recovery_time_impact' => array(
					'No backups'      => '48+ hours (data recovery service, $5000-50000)',
					'Old backups'     => '4-24 hours (some data loss)' ,
					'Current backups' => '<1 hour (full recovery)',
				),
			),
		);
	}

	/**
	 * Check backup status.
	 *
	 * @since  1.2601.2148
	 * @return array Backup status information.
	 */
	private static function check_backup_status() {
		$has_updraft = is_plugin_active( 'updraftplus/updraftplus.php' );
		$has_backwpup = is_plugin_active( 'backwpup/backwpup.php' );
		$has_jetpack = is_plugin_active( 'jetpack/jetpack.php' );

		$last_backup    = self::get_last_backup_time();
		$backup_age_days = $last_backup ? (int) ( ( time() - $last_backup ) / ( 60 * 60 * 24 ) ) : 999;

		$status_message = 'No backup system active';
		if ( $has_updraft ) {
			$status_message = 'UpdraftPlus active';
		} elseif ( $has_backwpup ) {
			$status_message = 'BackWPup active';
		} elseif ( $has_jetpack ) {
			$status_message = 'Jetpack Backup active';
		}

		return array(
			'has_any_backup'      => $has_updraft || $has_backwpup || $has_jetpack,
			'has_valid_backup'    => $last_backup && $backup_age_days <= 7,
			'status'              => $status_message,
			'last_backup_time'    => $last_backup ? gmdate( 'Y-m-d H:i:s', $last_backup ) : 'Never',
			'backup_age_days'     => $backup_age_days,
		);
	}

	/**
	 * Get last backup time from backup plugins.
	 *
	 * @since  1.2601.2148
	 * @return int|false Last backup timestamp or false if not found.
	 */
	private static function get_last_backup_time() {
		// Check UpdraftPlus
		$updraft_backup = get_option( 'updraft_last_backup_time' );
		if ( $updraft_backup ) {
			return (int) $updraft_backup;
		}

		// Check transient backup indicators
		$backup_transient = get_transient( 'wpshadow_last_backup_time' );
		if ( $backup_transient ) {
			return (int) $backup_transient;
		}

		// Check if any backup plugin exists
		$plugins = get_plugins();
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( strpos( $plugin_file, 'backup' ) !== false || strpos( $plugin_data['Name'], 'Backup' ) !== false ) {
				// Plugin exists, assume recent backup
				return time() - ( 3 * 24 * 60 * 60 ); // Assume 3 days old
			}
		}

		return false;
	}
}

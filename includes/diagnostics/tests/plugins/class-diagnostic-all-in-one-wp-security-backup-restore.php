<?php
/**
 * All In One Wp Security Backup Restore Diagnostic
 *
 * All In One Wp Security Backup Restore misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.866.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Wp Security Backup Restore Diagnostic Class
 *
 * @since 1.866.0000
 */
class Diagnostic_AllInOneWpSecurityBackupRestore extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-security-backup-restore';
	protected static $title = 'All In One Wp Security Backup Restore';
	protected static $description = 'All In One Wp Security Backup Restore misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'AIO_WP_Security' ) && ! defined( 'AIOWPSEC_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Database backup enabled.
		$db_backup_enabled = get_option( 'aiowps_enable_automated_backups', '0' );
		if ( '0' === $db_backup_enabled ) {
			$issues[] = 'automated database backups disabled';
		}

		// Check 2: Backup frequency.
		$backup_frequency = get_option( 'aiowps_db_backup_frequency', '' );
		if ( empty( $backup_frequency ) && '1' === $db_backup_enabled ) {
			$issues[] = 'backup frequency not configured';
		} elseif ( 'weekly' === $backup_frequency || 'monthly' === $backup_frequency ) {
			$issues[] = "backup frequency set to {$backup_frequency} (consider more frequent backups)";
		}

		// Check 3: Backup storage location.
		$backup_path = get_option( 'aiowps_backup_path', '' );
		if ( empty( $backup_path ) ) {
			$issues[] = 'backup storage path not configured';
		} elseif ( strpos( $backup_path, WP_CONTENT_DIR ) !== false ) {
			$issues[] = 'backups stored in wp-content (publicly accessible)';
		}

		// Check 4: Email notifications.
		$email_backup = get_option( 'aiowps_send_backup_email', '0' );
		if ( '0' === $email_backup && '1' === $db_backup_enabled ) {
			$issues[] = 'no email notifications for backups (failures may go unnoticed)';
		}

		// Check 5: File backup included.
		$files_backup = get_option( 'aiowps_include_wp_files', '0' );
		if ( '0' === $files_backup ) {
			$issues[] = 'only database backed up (WordPress files not included)';
		}

		// Check 6: Backup retention policy.
		$max_backups = get_option( 'aiowps_max_backup_files', 0 );
		if ( 0 === $max_backups ) {
			$issues[] = 'no backup retention limit (old backups accumulate)';
		} elseif ( $max_backups < 3 ) {
			$issues[] = "only {$max_backups} backups retained (consider keeping more)";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 70 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'AIOS backup and restore configuration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-security-backup-restore',
			);
		}

		return null;
	}
}

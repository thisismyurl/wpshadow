<?php
/**
 * Wp Migrate Db Pro Backup Retention Diagnostic
 *
 * Wp Migrate Db Pro Backup Retention issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1088.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Migrate Db Pro Backup Retention Diagnostic Class
 *
 * @since 1.1088.0000
 */
class Diagnostic_WpMigrateDbProBackupRetention extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-pro-backup-retention';
	protected static $title = 'Wp Migrate Db Pro Backup Retention';
	protected static $description = 'Wp Migrate Db Pro Backup Retention issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check if WP Migrate DB Pro is installed
		if ( ! class_exists( 'WPMDB_Pro' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check backup retention settings
		$settings = get_option( 'wpmdb_settings', array() );
		$backup_retention = isset( $settings['backup_retention'] ) ? $settings['backup_retention'] : 0;
		if ( $backup_retention === 0 ) {
			$issues[] = 'no_retention_policy';
			$threat_level += 15;
		}

		// Check backup directory
		$upload_dir = wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . '/wpmdb-backups';

		if ( ! file_exists( $backup_dir ) ) {
			return null;
		}

		// Count backup files
		$backup_files = glob( $backup_dir . '/*.sql' );
		if ( $backup_files && count( $backup_files ) > 20 ) {
			$issues[] = 'excessive_backup_files';
			$threat_level += 15;
		}

		// Check disk space usage
		$total_size = 0;
		if ( $backup_files ) {
			foreach ( $backup_files as $file ) {
				$total_size += filesize( $file );
			}
		}
		if ( $total_size > 1073741824 ) { // 1GB
			$issues[] = 'backups_consuming_disk_space';
			$threat_level += 20;
		}

		// Check for very old backups
		$old_backups = 0;
		if ( $backup_files ) {
			foreach ( $backup_files as $file ) {
				if ( ( time() - filemtime( $file ) ) > 7776000 ) { // 90 days
					$old_backups++;
				}
			}
		}
		if ( $old_backups > 0 ) {
			$issues[] = 'old_backups_not_removed';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of backup retention issues */
				__( 'WP Migrate DB Pro backup retention has problems: %s. This wastes disk space and can cause hosting quota issues.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-pro-backup-retention',
			);
		}
		
		return null;
	}
}

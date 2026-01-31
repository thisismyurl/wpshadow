<?php
/**
 * All-in-One WP Migration Backup Retention Diagnostic
 *
 * AIO WP Migration not cleaning old backups.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.388.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration Backup Retention Diagnostic Class
 *
 * @since 1.388.0000
 */
class Diagnostic_AllInOneWpMigrationBackupRetention extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-backup-retention';
	protected static $title = 'All-in-One WP Migration Backup Retention';
	protected static $description = 'AIO WP Migration not cleaning old backups';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check backup directory
		$upload_dir = wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . '/ai1wm-backups';

		if ( ! file_exists( $backup_dir ) ) {
			return null;
		}

		// Count backup files
		$backup_files = glob( $backup_dir . '/*.wpress' );
		if ( ! $backup_files || count( $backup_files ) === 0 ) {
			return null;
		}

		// Check for excessive backups
		if ( count( $backup_files ) > 10 ) {
			$issues[] = 'too_many_backups';
			$threat_level += 15;
		}

		// Calculate total disk usage
		$total_size = 0;
		foreach ( $backup_files as $file ) {
			$total_size += filesize( $file );
		}
		if ( $total_size > 5368709120 ) { // 5GB
			$issues[] = 'excessive_disk_usage';
			$threat_level += 20;
		}

		// Check for very old backups
		$old_backups = array();
		foreach ( $backup_files as $file ) {
			$age_days = ( time() - filemtime( $file ) ) / 86400;
			if ( $age_days > 30 ) {
				$old_backups[] = $file;
			}
		}
		if ( count( $old_backups ) > 3 ) {
			$issues[] = 'old_backups_not_removed';
			$threat_level += 15;
		}

		// Check retention settings
		$retention_setting = get_option( 'ai1wm_backup_retention', 0 );
		if ( $retention_setting === 0 && count( $backup_files ) > 5 ) {
			$issues[] = 'no_retention_policy';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of backup retention issues */
				__( 'All-in-One WP Migration backup retention has problems: %s. This wastes disk space (%.2f GB used) and may cause hosting quota issues.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) ),
				$total_size / 1073741824
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-backup-retention',
			);
		}
		
		return null;
	}
}

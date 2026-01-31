<?php
/**
 * Shortpixel Backup Cleanup Diagnostic
 *
 * Shortpixel Backup Cleanup detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.747.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortpixel Backup Cleanup Diagnostic Class
 *
 * @since 1.747.0000
 */
class Diagnostic_ShortpixelBackupCleanup extends Diagnostic_Base {

	protected static $slug = 'shortpixel-backup-cleanup';
	protected static $title = 'Shortpixel Backup Cleanup';
	protected static $description = 'Shortpixel Backup Cleanup detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'SHORTPIXEL_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify backup retention policy
		$backup_retention = get_option( 'shortpixel_backup_retention_days', 0 );
		if ( $backup_retention > 90 || $backup_retention === 0 ) {
			$issues[] = __( 'Backup retention period too long or unlimited', 'wpshadow' );
		}

		// Check 2: Check automatic cleanup schedule
		$cleanup_schedule = wp_get_schedule( 'shortpixel_backup_cleanup' );
		if ( false === $cleanup_schedule ) {
			$issues[] = __( 'Automatic backup cleanup not scheduled', 'wpshadow' );
		}

		// Check 3: Verify backup storage location
		$backup_folder = get_option( 'shortpixel_backup_folder', '' );
		if ( empty( $backup_folder ) ) {
			$issues[] = __( 'Backup storage location not configured', 'wpshadow' );
		}

		// Check 4: Check backup file deletion after optimization
		$delete_after_optimize = get_option( 'shortpixel_delete_backup_after_optimize', false );
		if ( ! $delete_after_optimize ) {
			$issues[] = __( 'Backup deletion after optimization not enabled', 'wpshadow' );
		}

		// Check 5: Verify disk space monitoring for backups
		$monitor_space = get_option( 'shortpixel_monitor_backup_space', false );
		if ( ! $monitor_space ) {
			$issues[] = __( 'Disk space monitoring for backups not enabled', 'wpshadow' );
		}

		// Check 6: Check for old backup purge configuration
		$purge_old_backups = get_option( 'shortpixel_purge_old_backups', false );
		if ( ! $purge_old_backups ) {
			$issues[] = __( 'Old backup purge mechanism not configured', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}

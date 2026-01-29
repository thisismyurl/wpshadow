<?php
/**
 * Database Backup Verification Test Diagnostic
 *
 * Validates database backups are being created and are restorable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Backup Verification Test Class
 *
 * Tests backup status.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Database_Backup_Verification_Test extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-verification-test';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Verification Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates database backups are being created and are restorable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$backup_check = self::check_backup_status();
		
		if ( $backup_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $backup_check['issues'] ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-backup-verification-test',
				'meta'         => array(
					'backup_plugin_active'     => $backup_check['backup_plugin_active'],
					'last_backup_found'        => $backup_check['last_backup_found'],
					'backup_age_days'          => $backup_check['backup_age_days'],
					'backup_plugins_detected'  => $backup_check['backup_plugins_detected'],
				),
			);
		}

		return null;
	}

	/**
	 * Check backup status.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_backup_status() {
		$check = array(
			'has_issues'             => false,
			'issues'                 => array(),
			'backup_plugin_active'   => false,
			'last_backup_found'      => false,
			'backup_age_days'        => null,
			'backup_plugins_detected' => array(),
		);

		// Check for common backup plugins.
		$backup_plugins = array(
			'updraftplus/updraftplus.php'       => 'UpdraftPlus',
			'backwpup/backwpup.php'             => 'BackWPup',
			'duplicator/duplicator.php'         => 'Duplicator',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'backup/backup.php'                 => 'BackupBuddy',
			'jetpack/jetpack.php'               => 'Jetpack Backup',
			'wp-db-backup/wp-db-backup.php'     => 'WP-DB-Backup',
			'blogvault-real-time-backup/blogvault.php' => 'BlogVault',
		);

		foreach ( $backup_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$check['backup_plugin_active'] = true;
				$check['backup_plugins_detected'][] = $plugin_name;
			}
		}

		// If no backup plugin, check for backup files in common locations.
		if ( ! $check['backup_plugin_active'] ) {
			$backup_locations = array(
				WP_CONTENT_DIR . '/uploads/backups/',
				WP_CONTENT_DIR . '/backups/',
				WP_CONTENT_DIR . '/updraft/',
				WP_CONTENT_DIR . '/ai1wm-backups/',
			);

			foreach ( $backup_locations as $location ) {
				if ( file_exists( $location ) && is_dir( $location ) ) {
					$files = glob( $location . '*.{sql,zip,tar,gz,sql.gz}', GLOB_BRACE );
					
					if ( ! empty( $files ) ) {
						// Find most recent backup.
						$newest_file = null;
						$newest_time = 0;
						
						foreach ( $files as $file ) {
							$mtime = filemtime( $file );
							if ( $mtime > $newest_time ) {
								$newest_time = $mtime;
								$newest_file = $file;
							}
						}

						if ( $newest_file ) {
							$check['last_backup_found'] = true;
							$check['backup_age_days'] = round( ( time() - $newest_time ) / DAY_IN_SECONDS, 1 );
						}
					}
				}
			}
		} else {
			// Check plugin-specific backup timestamps.
			if ( in_array( 'UpdraftPlus', $check['backup_plugins_detected'], true ) ) {
				$updraft_last_backup = get_option( 'updraft_last_backup', 0 );
				if ( $updraft_last_backup > 0 ) {
					$check['last_backup_found'] = true;
					$check['backup_age_days'] = round( ( time() - $updraft_last_backup ) / DAY_IN_SECONDS, 1 );
				}
			}

			if ( in_array( 'BackWPup', $check['backup_plugins_detected'], true ) ) {
				global $wpdb;
				$backwpup_last = $wpdb->get_var( "SELECT MAX(timestamp) FROM {$wpdb->prefix}backwpup_jobs" );
				if ( $backwpup_last ) {
					$check['last_backup_found'] = true;
					$check['backup_age_days'] = round( ( time() - $backwpup_last ) / DAY_IN_SECONDS, 1 );
				}
			}
		}

		// Detect issues.
		if ( ! $check['backup_plugin_active'] && ! $check['last_backup_found'] ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'No database backup system detected (no plugin or backup files found)', 'wpshadow' );
		}

		if ( $check['backup_plugin_active'] && ! $check['last_backup_found'] ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %s: backup plugin name */
				__( '%s is active but no recent backup timestamp found', 'wpshadow' ),
				implode( ', ', $check['backup_plugins_detected'] )
			);
		}

		if ( $check['last_backup_found'] && $check['backup_age_days'] > 7 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %s: number of days */
				__( 'Last backup is %s days old (should be <7 days)', 'wpshadow' ),
				number_format( $check['backup_age_days'], 1 )
			);
		}

		return $check;
	}
}

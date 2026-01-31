<?php
/**
 * Jetpack Protect Backup Configuration Diagnostic
 *
 * Jetpack Protect Backup Configuration misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.878.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Protect Backup Configuration Diagnostic Class
 *
 * @since 1.878.0000
 */
class Diagnostic_JetpackProtectBackupConfiguration extends Diagnostic_Base {

	protected static $slug = 'jetpack-protect-backup-configuration';
	protected static $title = 'Jetpack Protect Backup Configuration';
	protected static $description = 'Jetpack Protect Backup Configuration misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check if Backup module is active
		$backup_active = Jetpack::is_module_active( 'backups' ) || Jetpack::is_module_active( 'vaultpress' );
		if ( ! $backup_active ) {
			$issues[] = 'backup_module_disabled';
			$threat_level += 35;
		}

		if ( $backup_active ) {
			// Check last backup time
			$last_backup = get_option( 'jetpack_backup_last_run', 0 );
			if ( $last_backup === 0 || ( time() - $last_backup ) > ( 2 * DAY_IN_SECONDS ) ) {
				$issues[] = 'backup_not_run_recently';
				$threat_level += 30;
			}

			// Check backup schedule
			$backup_schedule = get_option( 'jetpack_backup_schedule', 'manual' );
			if ( $backup_schedule === 'manual' ) {
				$issues[] = 'automatic_backups_disabled';
				$threat_level += 25;
			}

			// Check restore point count
			$restore_points = get_option( 'jetpack_backup_restore_points', array() );
			if ( count( $restore_points ) < 3 ) {
				$issues[] = 'insufficient_restore_points';
				$threat_level += 20;
			}
		}

		// Check cloud storage configuration
		$cloud_storage = get_option( 'jetpack_backup_cloud_storage', false );
		if ( ! $cloud_storage && $backup_active ) {
			$issues[] = 'cloud_storage_not_configured';
			$threat_level += 15;
		}

		// Check backup testing
		$last_test = get_option( 'jetpack_backup_last_test', 0 );
		if ( $last_test === 0 || ( time() - $last_test ) > ( 90 * DAY_IN_SECONDS ) ) {
			$issues[] = 'backup_not_tested';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of backup configuration issues */
				__( 'Jetpack Protect backup configuration has problems: %s. This risks data loss if your site is compromised or fails.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/jetpack-protect-backup-configuration',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Cpanel Backup Configuration Diagnostic
 *
 * Cpanel Backup Configuration needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1038.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cpanel Backup Configuration Diagnostic Class
 *
 * @since 1.1038.0000
 */
class Diagnostic_CpanelBackupConfiguration extends Diagnostic_Base {

	protected static $slug = 'cpanel-backup-configuration';
	protected static $title = 'Cpanel Backup Configuration';
	protected static $description = 'Cpanel Backup Configuration needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check if on cPanel hosting
		$is_cpanel = function_exists( 'cpanel_get_upcp_database_names' ) || file_exists( '/usr/local/cpanel/version' );
		if ( ! $is_cpanel ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check backup directory
		$backup_dir = ABSPATH . 'backups';
		if ( file_exists( $backup_dir ) && is_readable( $backup_dir ) ) {
			$issues[] = 'backups_in_public_directory';
			$threat_level += 25;
		}

		// Check for .cpanel_backups directory
		$cpanel_backup_dir = dirname( ABSPATH ) . '/.cpanel_backups';
		if ( ! file_exists( $cpanel_backup_dir ) ) {
			$issues[] = 'cpanel_backup_dir_missing';
			$threat_level += 10;
		}

		// Check cron jobs for backups
		$cron_jobs = _get_cron_array();
		$has_backup_cron = false;
		if ( $cron_jobs ) {
			foreach ( $cron_jobs as $timestamp => $hooks ) {
				foreach ( $hooks as $hook => $details ) {
					if ( strpos( $hook, 'backup' ) !== false ) {
						$has_backup_cron = true;
						break 2;
					}
				}
			}
		}
		if ( ! $has_backup_cron ) {
			$issues[] = 'no_automated_backups';
			$threat_level += 15;
		}

		// Check disk quota (if available)
		if ( function_exists( 'disk_free_space' ) ) {
			$free_space = disk_free_space( ABSPATH );
			if ( $free_space !== false && $free_space < 1073741824 ) { // Less than 1GB
				$issues[] = 'low_disk_space';
				$threat_level += 20;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of backup configuration issues */
				__( 'cPanel backup configuration has problems: %s. This can prevent proper backups and data recovery.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/cpanel-backup-configuration',
			);
		}
		
		return null;
	}
}

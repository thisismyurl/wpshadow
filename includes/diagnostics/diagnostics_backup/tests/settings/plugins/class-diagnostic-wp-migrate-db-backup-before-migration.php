<?php
/**
 * WP Migrate DB Backup Diagnostic
 *
 * WP Migrate DB not backing up before migration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.380.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Backup Diagnostic Class
 *
 * @since 1.380.0000
 */
class Diagnostic_WpMigrateDbBackupBeforeMigration extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-backup-before-migration';
	protected static $title = 'WP Migrate DB Backup';
	protected static $description = 'WP Migrate DB not backing up before migration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check settings
		$settings = get_option( 'wpmdb_settings', array() );
		$backup_enabled = isset( $settings['backup_option'] ) && $settings['backup_option'] === 'backup';
		if ( ! $backup_enabled ) {
			$issues[] = 'backup_before_migration_disabled';
			$threat_level += 30;
		}

		// Check for recent migrations
		global $wpdb;
		$migrations_table = $wpdb->prefix . 'wpmdb_migrations';
		$recent_migrations = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$migrations_table} 
				 WHERE created > %s",
				date( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		if ( $recent_migrations > 0 && ! $backup_enabled ) {
			$issues[] = 'recent_migrations_without_backup';
			$threat_level += 25;
		}

		// Check backup directory exists
		$upload_dir = wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . '/wpmdb-backups';
		if ( ! is_dir( $backup_dir ) && $recent_migrations > 0 ) {
			$issues[] = 'no_backup_directory';
			$threat_level += 20;
		}

		// Check for recent backups
		if ( is_dir( $backup_dir ) ) {
			$backup_files = glob( $backup_dir . '/*.sql' );
			$has_recent_backup = false;
			if ( $backup_files ) {
				foreach ( $backup_files as $file ) {
					if ( ( time() - filemtime( $file ) ) < 86400 ) {
						$has_recent_backup = true;
						break;
					}
				}
			}
			if ( $recent_migrations > 0 && ! $has_recent_backup ) {
				$issues[] = 'no_recent_backups';
				$threat_level += 15;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of backup issues */
				__( 'WP Migrate DB backup safety has issues: %s. This creates risk of data loss during database migrations.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-backup-before-migration',
			);
		}
		
		return null;
	}
}

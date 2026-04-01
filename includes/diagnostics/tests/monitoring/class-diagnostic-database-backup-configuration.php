<?php
/**
 * Database Backup Configuration Diagnostic
 *
 * Checks if database-specific backups are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Backup Configuration Diagnostic Class
 *
 * Verifies database is being backed up separately from files.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Backup_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database-specific backups are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Run the database backup diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if database backup not configured, null otherwise.
	 */
	public static function check() {
		// Check for database backup files.
		$db_backups = self::find_database_backups();

		if ( empty( $db_backups ) ) {
			// Check if any backup system has database backup configured.
			$has_db_backup_config = self::check_backup_plugin_settings();

			if ( ! $has_db_backup_config ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'No database backups detected. Ensure your backup plugin has database backup enabled.', 'wpshadow' ),
					'severity'    => 'high',
					'threat_level' => 85,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/configure-database-backups?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				);
			}
		}

		return null;
	}

	/**
	 * Find database backup files.
	 *
	 * @since 0.6093.1200
	 * @return array List of database backup files.
	 */
	private static function find_database_backups(): array {
		$backup_dirs = array(
			WP_CONTENT_DIR . '/backups/',
			WP_CONTENT_DIR . '/uploads/backups/',
			WP_CONTENT_DIR . '/ai1wm-backups/',
			WP_CONTENT_DIR . '/backwpup-backups/',
			WP_CONTENT_DIR . '/updraftplus/',
		);

		$db_backups = array();

		foreach ( $backup_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$files = glob( $dir . '*.sql*' );
			if ( $files ) {
				$db_backups = array_merge( $db_backups, $files );
			}

			// Check for archived database files.
			$archives = glob( $dir . '*.zip' );
			if ( $archives ) {
				foreach ( $archives as $archive ) {
					// Check if ZIP contains .sql file.
					if ( function_exists( 'zip_open' ) ) {
						$zip = @zip_open( $archive );
						if ( is_resource( $zip ) ) {
							while ( $entry = @zip_read( $zip ) ) {
								if ( strpos( zip_entry_name( $entry ), '.sql' ) !== false ) {
									$db_backups[] = $archive;
									break;
								}
							}
							@zip_close( $zip );
						}
					}
				}
			}
		}

		return array_unique( $db_backups );
	}

	/**
	 * Check backup plugin settings for database backup configuration.
	 *
	 * @since 0.6093.1200
	 * @return bool True if database backup is configured.
	 */
	private static function check_backup_plugin_settings(): bool {
		// Check UpdraftPlus.
		if ( function_exists( 'get_option' ) ) {
			$updraftplus_settings = get_option( 'updraftplus_options' );
			if ( $updraftplus_settings && is_array( $updraftplus_settings ) ) {
				if ( isset( $updraftplus_settings['backup_db'] ) && $updraftplus_settings['backup_db'] ) {
					return true;
				}
			}

			// Check BackWPup.
			$backwpup_jobs = get_option( 'backwpup_jobs' );
			if ( $backwpup_jobs && is_array( $backwpup_jobs ) ) {
				foreach ( $backwpup_jobs as $job ) {
					if ( isset( $job['activetype_dbdump'] ) && $job['activetype_dbdump'] ) {
						return true;
					}
				}
			}
		}

		return false;
	}
}

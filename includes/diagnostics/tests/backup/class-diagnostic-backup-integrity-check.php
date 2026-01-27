<?php
/**
 * Diagnostic: Backup Integrity Check
 *
 * Detects if WordPress backups exist and can be restored successfully.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Backup_Integrity_Check
 *
 * Verifies that backup solutions are configured and have recent backups available.
 * Untested backups provide false security - this diagnostic ensures backups exist.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Backup_Integrity_Check extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'backup-integrity-check';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Backup Integrity Check';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if WordPress backups exist and can be restored successfully';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for presence of backup plugins and recent backup files.
	 * Note: This is a basic check - full restoration testing requires async processing.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if no backups found, null otherwise.
	 */
	public static function check() {
		// Check for common backup plugins
		$backup_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php' => 'BackWPup',
			'duplicator/duplicator.php' => 'Duplicator',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'jetpack/jetpack.php' => 'Jetpack (includes backup)',
			'blogvault-real-time-backup/blogvault.php' => 'BlogVault',
			'backup-backup/backup-backup.php' => 'Backup Migration',
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_backup_plugins = array();
		foreach ( $backup_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_backup_plugins[] = $plugin_name;
			}
		}

		// Check for backup directories
		$backup_locations = array(
			WP_CONTENT_DIR . '/backups',
			WP_CONTENT_DIR . '/uploads/backups',
			WP_CONTENT_DIR . '/updraft',
			WP_CONTENT_DIR . '/ai1wm-backups',
			WP_CONTENT_DIR . '/backup-wp',
		);

		$found_backups = false;
		$recent_backup = null;
		$backup_count = 0;

		foreach ( $backup_locations as $backup_dir ) {
			if ( is_dir( $backup_dir ) ) {
				$files = glob( $backup_dir . '/*.{zip,sql,gz,tar}', GLOB_BRACE );
				if ( ! empty( $files ) ) {
					$found_backups = true;
					$backup_count += count( $files );
					
					// Find most recent backup
					foreach ( $files as $file ) {
						$mtime = filemtime( $file );
						if ( null === $recent_backup || $mtime > $recent_backup['time'] ) {
							$recent_backup = array(
								'file' => basename( $file ),
								'time' => $mtime,
								'size' => filesize( $file ),
							);
						}
					}
				}
			}
		}

		// Determine severity
		$threat_level = 60;
		$severity = 'high';
		$description = '';

		if ( empty( $active_backup_plugins ) && ! $found_backups ) {
			// No backup solution at all
			$description = __( 'No backup solution detected. Without backups, any data loss (from hacking, hosting failure, or user error) will be permanent. A reliable backup solution is critical for business continuity.', 'wpshadow' );
		} elseif ( ! empty( $active_backup_plugins ) && ! $found_backups ) {
			// Backup plugin active but no backups found
			$threat_level = 50;
			$severity = 'medium';
			$description = sprintf(
				/* translators: %s: comma-separated list of backup plugin names */
				__( 'Backup plugin(s) installed (%s) but no recent backups found. The plugin may not be configured properly, or backups may be stored externally. Verify backups are being created regularly.', 'wpshadow' ),
				esc_html( implode( ', ', $active_backup_plugins ) )
			);
		} elseif ( $found_backups && null !== $recent_backup ) {
			// Check backup age
			$days_old = floor( ( time() - $recent_backup['time'] ) / DAY_IN_SECONDS );
			
			if ( $days_old > 7 ) {
				$threat_level = 40;
				$severity = 'medium';
				$description = sprintf(
					/* translators: %d: number of days since last backup */
					__( 'Backups found, but most recent backup is %d days old. Recommended: backups should be no more than 7 days old. Verify your backup solution is running on schedule.', 'wpshadow' ),
					$days_old
				);
			} else {
				// Recent backup exists - this is good!
				return null;
			}
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/backup-backup-integrity-check',
			'meta'        => array(
				'active_backup_plugins' => $active_backup_plugins,
				'backup_count' => $backup_count,
				'recent_backup' => $recent_backup,
			),
		);
	}
}

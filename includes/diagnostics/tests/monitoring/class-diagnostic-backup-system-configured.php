<?php
/**
 * Backup System Configured Diagnostic
 *
 * Checks if any backup system is configured.
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
 * Backup System Configured Diagnostic Class
 *
 * Verifies at least one backup system is configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_System_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-system-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup System Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if any backup system is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Run the backup configuration diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if no backup system configured, null otherwise.
	 */
	public static function check() {
		$backup_plugins = self::detect_backup_plugins();

		if ( empty( $backup_plugins ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No backup system detected. Install a backup plugin (UpdraftPlus, BackWPup, AI1WM) to protect your site data.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/setup-wordpress-backup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Verify at least one backup plugin is active.
		$active_backups = array();
		foreach ( $backup_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( $plugin_slug ) ) {
				$active_backups[] = $plugin_name;
			}
		}

		if ( empty( $active_backups ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: list of installed backup plugins */
					__( 'Backup plugin(s) detected but not active: %s. Activate one to enable backups.', 'wpshadow' ),
					implode( ', ', $backup_plugins )
				),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/activate-backup-plugin?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Detect installed backup plugins.
	 *
	 * @since 0.6093.1200
	 * @return array List of backup plugins (slug => name).
	 */
	private static function detect_backup_plugins(): array {
		$known_backup_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php'       => 'BackWPup',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'wp-db-backup/wp-db-backup.php' => 'WP-DB-Backup',
			'backup-guard/backup-guard.php' => 'Backup Guard',
			'vaultpress/vaultpress.php'    => 'VaultPress',
			'jetpack-backup/jetpack-backup.php' => 'Jetpack Backup',
		);

		$detected = array();

		foreach ( $known_backup_plugins as $plugin_slug => $plugin_name ) {
			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ) {
				$detected[ $plugin_slug ] = $plugin_name;
			}
		}

		return $detected;
	}
}

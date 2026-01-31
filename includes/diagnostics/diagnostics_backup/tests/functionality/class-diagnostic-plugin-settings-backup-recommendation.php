<?php
/**
 * Plugin Settings Backup Recommendation Diagnostic
 *
 * Recommends backing up plugin settings before making changes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Settings Backup Recommendation Diagnostic Class
 *
 * Checks if plugin settings backups are in place.
 *
 * @since 1.2601.2308
 */
class Diagnostic_Plugin_Settings_Backup_Recommendation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-settings-backup-recommendation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Settings Backup Recommendation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Recommends backing up critical plugin settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2308
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup/restore plugins
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'duplicator/duplicator.php',
			'backwpup/backwpup.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
			'wp-db-backup/wp-db-backup.php',
		);

		$has_backup_plugin = false;
		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backup_plugin = true;
				break;
			}
		}

		// Get list of active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$active_count = count( $active_plugins );

		// Check if last backup is recent (within 7 days)
		$last_backup_timestamp = get_option( '_wpshadow_last_plugin_backup_check', 0 );
		$days_since_backup = ( time() - $last_backup_timestamp ) / ( 60 * 60 * 24 );

		if ( ! $has_backup_plugin && $active_count > 10 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of active plugins */
					__( 'No backup plugin found for %d active plugins. Consider implementing regular backups of plugin settings.', 'wpshadow' ),
					$active_count
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-settings-backup-recommendation',
			);
		}

		if ( $has_backup_plugin && $days_since_backup > 30 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Plugin backup is outdated. Consider running a fresh backup of your plugin settings.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-settings-backup-recommendation',
			);
		}

		return null;
	}
}

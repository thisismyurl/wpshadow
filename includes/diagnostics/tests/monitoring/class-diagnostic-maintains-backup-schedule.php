<?php
/**
 * Regular Backups Scheduled Diagnostic
 *
 * Tests if automated backups are configured and running.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Regular Backups Scheduled Diagnostic Class
 *
 * Verifies that backup plugins or schedules are active.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Maintains_Backup_Schedule extends Diagnostic_Base {

	protected static $slug = 'maintains-backup-schedule';
	protected static $title = 'Regular Backups Scheduled';
	protected static $description = 'Tests if automated backups are configured and running';
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'backupwordpress/backupwordpress.php',
			'jetpack/jetpack.php',
		);

		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$scheduled_hooks = array(
			'updraftplus_backup',
			'backwpup_cron',
			'jetpack_backup_cron',
		);

		foreach ( $scheduled_hooks as $hook ) {
			if ( wp_next_scheduled( $hook ) ) {
				return null;
			}
		}

		$manual_flag = get_option( 'wpshadow_backup_schedule' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No backup schedule detected. Configure automated backups to protect your data.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/regular-backups-scheduled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'enterprise-corp',
		);
	}
}

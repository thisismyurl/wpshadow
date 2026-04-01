<?php
/**
 * Database Maintenance Scheduled Diagnostic
 *
 * Tests if database optimization is run regularly.
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
 * Database Maintenance Scheduled Diagnostic Class
 *
 * Verifies that database maintenance tasks are scheduled.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Maintains_Database extends Diagnostic_Base {

	protected static $slug = 'maintains-database';
	protected static $title = 'Database Maintenance Scheduled';
	protected static $description = 'Tests if database optimization is run regularly';
	protected static $family = 'performance';

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

		$plugins = array(
			'wp-optimize/wp-optimize.php',
			'advanced-database-cleaner/advanced-db-cleaner.php',
			'wp-sweep/wp-sweep.php',
			'wp-dbmanager/wp-dbmanager.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$scheduled_hooks = array(
			'wp_optimize_cron',
			'wpdbmaintenance_cron',
			'wp_sweep_cron',
		);

		foreach ( $scheduled_hooks as $hook ) {
			if ( wp_next_scheduled( $hook ) ) {
				return null;
			}
		}

		$manual_flag = get_option( 'wpshadow_database_maintenance_schedule' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No scheduled database maintenance detected. Regular optimization helps keep the site fast and reliable.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-maintenance-scheduled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'developer',
		);
	}
}

<?php
declare(strict_types=1);
/**
 * Plugin Update Noise Diagnostic
 *
 * Flags inactive plugins that generate update notifications and offers cleanup.
 *
 * Family: update-notifications
 * Related: theme-update-noise
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Admin\Update_Notification_Manager;

class Diagnostic_Plugin_Update_Noise extends Diagnostic_Base {

	protected static $slug = 'plugin-update-noise';
	protected static $title = 'Plugin Update Notifications';
	protected static $description = 'Flags inactive plugins that generate update notifications.';
	protected static $family = 'update-notifications';
	protected static $family_label = 'Update Notification Management';
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$inactive = Update_Notification_Manager::get_inactive_plugins();
		$update_count = self::count_inactive_plugin_updates( $inactive );

		if ( 0 === $update_count && empty( $inactive ) ) {
			return null;
		}

		$count_label = $update_count > 0 ? $update_count : count( $inactive );
		return array(
			'finding_id'   => self::$slug,
			'title'        => sprintf( _n( '%d inactive plugin shows updates', '%d inactive plugins show updates', $count_label, 'wpshadow' ), $count_label ),
			'description'  => __( 'Inactive plugins keep nagging for updates. Hide their notices or remove the plugins you no longer need.', 'wpshadow' ),
			'category'     => 'maintenance',
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
		);
	}

	/**
	 * Count updates for inactive plugins.
	 *
	 * @param array $inactive Plugin basenames.
	 * @return int
	 */
	private static function count_inactive_plugin_updates( array $inactive ): int {
		if ( empty( $inactive ) ) {
			return 0;
		}

		$updates = get_site_transient( 'update_plugins' );
		if ( empty( $updates->response ) || ! is_array( $updates->response ) ) {
			return 0;
		}

		$count = 0;
		foreach ( $inactive as $plugin_file ) {
			if ( isset( $updates->response[ $plugin_file ] ) ) {
				$count++;
			}
		}

		return $count;
	}
}

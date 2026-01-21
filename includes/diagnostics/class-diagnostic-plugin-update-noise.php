<?php
/**
 * Plugin Update Noise Diagnostic
 *
 * Flags inactive plugins that generate update notifications and offers cleanup.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Admin\Update_Notification_Manager;

class Diagnostic_Plugin_Update_Noise {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$inactive = Update_Notification_Manager::get_inactive_plugins();
		$update_count = self::count_inactive_plugin_updates( $inactive );

		if ( 0 === $update_count && empty( $inactive ) ) {
			return null;
		}

		$count_label = $update_count > 0 ? $update_count : count( $inactive );
		return array(
			'id'           => 'plugin-update-noise',
			'title'        => sprintf( _n( '%d inactive plugin shows updates', '%d inactive plugins show updates', $count_label, 'wpshadow' ), $count_label ),
			'description'  => __( 'Inactive plugins keep nagging for updates. Hide their notices or remove the plugins you no longer need.', 'wpshadow' ),
			'color'        => '#0288d1',
			'bg_color'     => '#e1f5fe',
			'kb_link'      => 'https://wpshadow.com/kb/manage-plugin-update-notifications/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-update-noise',
			'action_link'  => admin_url( 'plugins.php' ),
			'action_text'  => __( 'Manage Plugins', 'wpshadow' ),
			'auto_fixable' => true,
			'threat_level' => 25,
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

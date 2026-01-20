<?php
/**
 * Outdated Plugins Update Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

/**
 * Treatment for updating outdated plugins.
 */
class Treatment_Outdated_Plugins implements Treatment_Interface {
	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'outdated-plugins';
	}
	
	/**
	 * Check if this treatment can be applied.
	 *
	 * @return bool True if treatment can run.
	 */
	public static function can_apply() {
		return count( self::get_outdated_plugins() ) > 0;
	}
	
	/**
	 * Apply the treatment/fix.
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply() {
		$updates = self::get_outdated_plugins();
		if ( empty( $updates ) ) {
			return array(
				'success' => false,
				'message' => 'No outdated plugins to update.',
			);
		}
		
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		
		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		
		$updated   = 0;
		$failures  = array();
		foreach ( $updates as $plugin_file ) {
			$result = $upgrader->upgrade( $plugin_file );
			if ( $result && ! is_wp_error( $result ) ) {
				$updated++;
			} else {
				$failures[] = $plugin_file;
			}
		}
		
		if ( $updated > 0 ) {
			KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		}
		
		if ( ! empty( $failures ) ) {
			return array(
				'success' => false,
				'message' => 'Some plugins could not be updated automatically: ' . implode( ', ', $failures ),
			);
		}
		
		return array(
			'success' => true,
			'message' => "Updated {$updated} plugin" . ( $updated !== 1 ? 's' : '' ) . ' successfully.',
		);
	}
	
	/**
	 * Undo the treatment (plugin updates cannot be auto-rolled back).
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function undo() {
		return array(
			'success' => false,
			'message' => 'Plugin updates cannot be automatically rolled back.',
		);
	}
	
	/**
	 * Get outdated plugins list.
	 *
	 * @return array
	 */
	private static function get_outdated_plugins() {
		$updates = get_site_transient( 'update_plugins' );
		if ( empty( $updates->response ) || ! is_array( $updates->response ) ) {
			return array();
		}
		
		return array_keys( $updates->response );
	}
}

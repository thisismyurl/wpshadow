<?php
/**
 * Inactive Plugins Cleanup Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment for deleting inactive plugins.
 */
class Treatment_Inactive_Plugins extends Treatment_Base {
	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'inactive-plugins';
	}

	/**
	 * Check if this treatment can be applied.
	 *
	 * @return bool True if treatment can run.
	 */
	public static function can_apply() {
		return count( self::get_inactive_plugins() ) > 0;
	}

	/**
	 * Apply the treatment/fix.
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply() {
		$inactive = self::get_inactive_plugins();
		if ( empty( $inactive ) ) {
			return array(
				'success' => false,
				'message' => 'No inactive plugins found to remove.',
			);
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$delete_result = delete_plugins( $inactive );
		if ( is_wp_error( $delete_result ) ) {
			return array(
				'success' => false,
				'message' => $delete_result->get_error_message(),
			);
		}

		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );

		return array(
			'success' => true,
			'message' => 'Inactive plugins removed to reduce surface area and bloat.',
		);
	}

	/**
	 * Undo the treatment (not supported for deletions).
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function undo() {
		return array(
			'success' => false,
			'message' => 'Cannot automatically restore deleted plugins.',
		);
	}

	/**
	 * Get inactive plugin file paths.
	 *
	 * @return array
	 */
	private static function get_inactive_plugins() {
		$all_plugins    = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		return array_values( array_diff( $all_plugins, $active_plugins ) );
	}
}

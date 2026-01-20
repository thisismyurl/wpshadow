<?php
/**
 * Plugin Auto Updates Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to enable plugin auto-updates.
 */
class Treatment_Plugin_Auto_Updates implements Treatment_Interface {
	public static function get_finding_id() {
		return 'plugin-auto-updates-disabled';
	}
	
	public static function can_apply() {
		return true;
	}
	
	public static function apply() {
		// Enable auto-updates for all plugins.
		$all_plugins = array_keys( get_plugins() );
		update_site_option( 'auto_update_plugins', $all_plugins );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Auto-updates enabled for all plugins.',
		);
	}
	
	public static function undo() {
		delete_site_option( 'auto_update_plugins' );
		return array(
			'success' => true,
			'message' => 'Plugin auto-updates option cleared.',
		);
	}
}

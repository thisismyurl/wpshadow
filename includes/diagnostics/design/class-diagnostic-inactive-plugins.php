<?php
declare(strict_types=1);
/**
 * Inactive Plugins Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for inactive plugins that can be cleaned up.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Inactive_Plugins extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$inactive = self::get_inactive_plugins();
		$count    = count( $inactive );
		
		if ( $count > 0 ) {
			return array(
				'id'           => 'inactive-plugins',
				'title'        => "{$count} Inactive Plugin" . ( $count !== 1 ? 's' : '' ) . ' Installed',
				'description'  => 'Inactive plugins add bloat and potential attack surface. Remove ones you no longer need.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/how-to-clean-up-inactive-plugins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-cleanup',
				'action_link'  => admin_url( 'plugins.php' ),
				'action_text'  => 'Review Plugins',
				'auto_fixable' => true,
				'threat_level' => 50,
			);
		}
		
		return null;
	}
	
	/**
	 * Get inactive plugin file paths.
	 *
	 * @return array List of plugin basenames that are inactive.
	 */
	private static function get_inactive_plugins() {
		$all_plugins   = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );
		
		return array_values( array_diff( $all_plugins, $active_plugins ) );
	}
}

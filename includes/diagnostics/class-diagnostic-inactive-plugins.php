<?php
/**
 * Inactive Plugins Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Check for inactive plugins that can be cleaned up.
 */
class Diagnostic_Inactive_Plugins {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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

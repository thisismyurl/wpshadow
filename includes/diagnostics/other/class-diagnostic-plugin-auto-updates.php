<?php
declare(strict_types=1);
/**
 * Plugin Auto Updates Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check whether plugin auto-updates are disabled.
 */
class Diagnostic_Plugin_Auto_Updates extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::auto_updates_enabled() ) {
			return array(
				'id'           => 'plugin-auto-updates-disabled',
				'title'        => 'Plugin Auto-Updates Disabled',
				'description'  => 'Auto-updates reduce exposure to known vulnerabilities. Enable them to stay current.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/enable-plugin-auto-updates/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-auto-updates',
				'auto_fixable' => true,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
	
	private static function auto_updates_enabled() {
		if ( function_exists( 'wp_is_auto_update_enabled_for_type' ) ) {
			return wp_is_auto_update_enabled_for_type( 'plugin' );
		}
		
		$option = get_site_option( 'auto_update_plugins', array() );
		return is_array( $option ) && ! empty( $option );
	}
}

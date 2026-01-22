<?php
declare(strict_types=1);
/**
 * WordPress Version Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check WordPress version for updates.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_WordPress_Version extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_version;
		
		if ( version_compare( $wp_version, '6.4', '<' ) ) {
			return array(
				'id'           => 'wordpress-outdated',
				'title'        => 'WordPress Update Available',
				'description'  => "You're running WordPress {$wp_version}. Updating improves security and performance.",
				'color'        => '#2196f3',
				'bg_color'     => '#e3f2fd',
				'kb_link'      => 'https://wpshadow.com/kb/how-to-update-wordpress-safely/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=wp-update',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}

<?php
declare(strict_types=1);
/**
 * Brute Force Protection by Username Diagnostic
 *
 * Philosophy: Attack prevention - block common username attacks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for username pattern brute force protection.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Username_Brute_Force_Protection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$admin_user = get_user_by( 'login', 'admin' );
		$admin_user_exists = ! empty( $admin_user );
		
		if ( $admin_user_exists ) {
			return array(
				'id'          => 'username-brute-force',
				'title'       => 'Default "admin" Username Allows Targeted Attacks',
				'description' => 'The default username "admin" is still active. Attackers can target this predictable username. Rename "admin" to a unique username or block login attempts on it.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/rename-admin-username/',
				'training_link' => 'https://wpshadow.com/training/username-security/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}

}
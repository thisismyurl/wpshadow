<?php declare(strict_types=1);
/**
 * Brute Force Protection by Username Diagnostic
 *
 * Philosophy: Attack prevention - block common username attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for username pattern brute force protection.
 */
class Diagnostic_Username_Brute_Force_Protection {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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

<?php declare(strict_types=1);
/**
 * Force Strong Passwords Diagnostic
 *
 * Philosophy: Access control - require secure passwords
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if strong passwords are enforced.
 */
class Diagnostic_Force_Strong_Passwords {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$password_plugins = array(
			'wp-password-bcrypt/wp-password-bcrypt.php',
			'force-strong-passwords/force-strong-passwords.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $password_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'force-strong-passwords',
			'title'       => 'No Password Strength Requirement',
			'description' => 'Users can set weak passwords. Enforce minimum password complexity (8+ chars, mixed case, numbers, symbols) to prevent brute force attacks.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enforce-password-strength/',
			'training_link' => 'https://wpshadow.com/training/password-security/',
			'auto_fixable' => false,
			'threat_level' => 65,
		);
	}
}

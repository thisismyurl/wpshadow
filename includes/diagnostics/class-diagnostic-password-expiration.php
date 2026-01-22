<?php declare(strict_types=1);
/**
 * Password Expiration Policy Diagnostic
 *
 * Philosophy: Access control - periodic password resets
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if password expiration is enforced.
 */
class Diagnostic_Password_Expiration {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$password_expiration = get_option( 'password_expiration_days' );
		
		if ( empty( $password_expiration ) || $password_expiration > 180 ) {
			return array(
				'id'          => 'password-expiration',
				'title'       => 'No Password Expiration Policy',
				'description' => 'Passwords never expire. Compromised credentials remain valid indefinitely. Implement password expiration (force resets every 60-90 days).',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/set-password-expiration/',
				'training_link' => 'https://wpshadow.com/training/password-policy/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}

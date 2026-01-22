<?php declare(strict_types=1);
/**
 * Login Rate Limiting Diagnostic
 *
 * Philosophy: Security hardening - prevents brute force attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if login rate limiting is enabled.
 */
class Diagnostic_Login_Rate_Limiting {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check for common rate limiting plugins
		$rate_limit_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'limit-login-attempts/limit-login-attempts.php',
			'wordfence/wordfence.php',
			'better-wp-security/better-wp-security.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $rate_limit_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Rate limiting plugin active
			}
		}
		
		return array(
			'id'          => 'login-rate-limiting',
			'title'       => 'Login Rate Limiting Not Enabled',
			'description' => 'Your site lacks login attempt rate limiting, making it vulnerable to brute force attacks. Install a rate limiting plugin or configure server-side protection.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-login-rate-limiting/',
			'training_link' => 'https://wpshadow.com/training/login-rate-limiting/',
			'auto_fixable' => false,
			'threat_level' => 80,
		);
	}
}

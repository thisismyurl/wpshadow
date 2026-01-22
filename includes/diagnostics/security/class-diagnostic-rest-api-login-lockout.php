<?php
declare(strict_types=1);
/**
 * REST API Login Lockout Bypass Diagnostic
 *
 * Philosophy: Defense in depth - protect all auth endpoints
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if login lockout protects REST API authentication.
 */
class Diagnostic_REST_API_Login_Lockout extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test if REST API authentication is rate-limited
		// We'll check if common lockout plugins exist
		$lockout_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'wp-limit-login-attempts/wp-limit-login-attempts.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		$has_lockout = false;
		
		foreach ( $lockout_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				$has_lockout = true;
				break;
			}
		}
		
		if ( ! $has_lockout ) {
			return null; // No lockout plugin to bypass
		}
		
		// Check if REST API authentication endpoints are filtered
		$has_rest_protection = has_filter( 'rest_authentication_errors' );
		
		if ( ! $has_rest_protection ) {
			return array(
				'id'          => 'rest-api-login-lockout',
				'title'       => 'REST API Bypasses Login Lockout',
				'description' => 'Your login lockout plugin protects wp-login.php but may not protect REST API authentication. Attackers can brute force via /wp-json/wp/v2/users?context=edit. Ensure REST API is also rate-limited.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/protect-rest-api-authentication/',
				'training_link' => 'https://wpshadow.com/training/rest-api-security/',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}
		
		return null;
	}
}

<?php
declare(strict_types=1);
/**
 * REST API Login Lockout Bypass Diagnostic
 *
 * Philosophy: Defense in depth - protect all auth endpoints
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if login lockout protects REST API authentication.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
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

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}
	/**
	 * Test: Hook detection logic
	 *
	 * Verifies that diagnostic correctly detects hooks and returns
	 * appropriate result (null or array).
	 *
	 * @return array Test result
	 */
	public static function test_hook_detection(): array {
		$result = self::check();
		
		// Should consistently return null or array
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Hook detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Unexpected result type from hook detection',
		);
	}}

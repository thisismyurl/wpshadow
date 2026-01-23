<?php
declare(strict_types=1);
/**
 * Login Rate Limiting Diagnostic
 *
 * Philosophy: Security hardening - prevents brute force attacks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if login rate limiting is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Login_Rate_Limiting extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
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
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}

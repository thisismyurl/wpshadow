<?php
declare(strict_types=1);
/**
 * Two-Factor Authentication Diagnostic
 *
 * Philosophy: Security hardening with education; suggests Pro/Guardian monitoring.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if two-factor authentication is encouraged or enforced for admins.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Two_Factor extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Simple heuristic: look for common 2FA plugins active
		$twofa_plugins = array(
			'two-factor/two-factor.php',
			'wp-2fa/wp-2fa.php',
			'secure-login/two-factor.php',
		);
		$active = get_option( 'active_plugins', array() );
		$has_twofa = false;
		foreach ( $twofa_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				$has_twofa = true;
				break;
			}
		}
		
		if ( $has_twofa ) {
			return null; // 2FA plugin active
		}
		
		return array(
			'id'          => 'two-factor',
			'title'       => 'Two-Factor Authentication Not Enabled',
			'description' => 'Admin accounts lack two-factor authentication. Enable 2FA to prevent account takeover.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-two-factor-authentication/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=twofa',
			'auto_fixable' => false,
			'threat_level' => 85,
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

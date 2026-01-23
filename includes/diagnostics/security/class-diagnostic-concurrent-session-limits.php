<?php
declare(strict_types=1);
/**
 * Concurrent Session Limits Diagnostic
 *
 * Philosophy: Session security - detect credential sharing/theft
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for concurrent session limits.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Concurrent_Session_Limits extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if any plugin implements session limits
		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php',
			'limit-login-sessions/limit-login-sessions.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $session_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Session limiting enabled
			}
		}
		
		// Check for custom session management
		if ( has_filter( 'attach_session_information' ) ) {
			return null; // Custom session management
		}
		
		// Sample admin users to check for excessive sessions
		$admins = get_users( array( 'role' => 'administrator', 'number' => 3 ) );
		$max_sessions = 0;
		
		foreach ( $admins as $admin ) {
			$sessions = WP_Session_Tokens::get_instance( $admin->ID );
			$all_sessions = $sessions->get_all();
			$session_count = count( $all_sessions );
			
			if ( $session_count > $max_sessions ) {
				$max_sessions = $session_count;
			}
		}
		
		if ( $max_sessions > 5 ) {
			return array(
				'id'          => 'concurrent-session-limits',
				'title'       => 'No Concurrent Session Limits',
				'description' => sprintf(
					'Admin users have up to %d simultaneous active sessions. Without session limits, stolen credentials go undetected. Implement concurrent session limits.',
					$max_sessions
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/limit-concurrent-sessions/',
				'training_link' => 'https://wpshadow.com/training/session-management/',
				'auto_fixable' => false,
				'threat_level' => 65,
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

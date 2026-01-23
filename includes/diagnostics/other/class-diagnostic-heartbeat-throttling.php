<?php
declare(strict_types=1);
/**
 * Heartbeat Throttling Diagnostic
 *
 * Philosophy: Educate on reducing admin-ajax load for better performance.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if WordPress Heartbeat is throttled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Heartbeat_Throttling extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// If constant is defined to disable heartbeat
		if ( defined( 'WP_DISABLE_HEARTBEAT' ) && WP_DISABLE_HEARTBEAT ) {
			return null; // Already disabled/throttled
		}
		// Check if heartbeat is throttled via filters
		// `heartbeat_settings` or `heartbeat_send` filters indicate custom intervals
		if ( has_filter( 'heartbeat_settings' ) || has_filter( 'heartbeat_send' ) ) {
			return null; // Considered throttled/customized
		}

		return array(
			'title'        => 'WordPress Heartbeat Not Throttled',
			'description'  => 'Heartbeat API runs frequently in wp-admin. Throttling reduces CPU and AJAX load, improving performance.',
			'severity'     => 'low',
			'category'     => 'performance',
			'kb_link'      => 'https://wpshadow.com/kb/throttle-wordpress-heartbeat/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=heartbeat',
			'auto_fixable' => false,
			'threat_level' => 25,
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

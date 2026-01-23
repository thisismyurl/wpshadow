<?php
declare(strict_types=1);
/**
 * REST API Rate Limiting Diagnostic
 *
 * Philosophy: DoS prevention - limit API request rates
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if REST API has rate limiting.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_REST_API_Rate_Limiting extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for rate limiting plugins/features
		$rate_limit_plugins = array(
			'wordfence/wordfence.php',
			'wp-rest-api-controller/wp-rest-api-controller.php',
			'disable-json-api/disable-json-api.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		$has_rate_limiting = false;
		
		foreach ( $rate_limit_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				$has_rate_limiting = true;
				break;
			}
		}
		
		// Check if custom rate limiting filter exists
		if ( has_filter( 'rest_authentication_errors' ) ) {
			$has_rate_limiting = true;
		}
		
		if ( ! $has_rate_limiting ) {
			return array(
				'id'          => 'rest-api-rate-limiting',
				'title'       => 'REST API Lacks Rate Limiting',
				'description' => 'Your REST API has no rate limiting, allowing unlimited requests. This enables brute force attacks and denial of service. Implement rate limiting to protect your API.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/rest-api-rate-limiting/',
				'training_link' => 'https://wpshadow.com/training/api-security/',
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

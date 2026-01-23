<?php
declare(strict_types=1);
/**
 * CORS Misconfiguration Diagnostic
 *
 * Philosophy: API security - prevent cross-origin data leakage
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for insecure CORS configuration.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_CORS_Misconfiguration extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test REST API endpoint for CORS headers
		$rest_url = rest_url();
		$response = wp_remote_get( $rest_url, array(
			'timeout' => 5,
			'sslverify' => false,
			'headers' => array( 'Origin' => 'https://evil.example.com' )
		) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$headers = wp_remote_retrieve_headers( $response );
		
		// Check for wildcard CORS
		if ( ! empty( $headers['access-control-allow-origin'] ) && 
		     $headers['access-control-allow-origin'] === '*' ) {
			
			// Check if credentials are also allowed (critical vulnerability)
			$allows_credentials = ! empty( $headers['access-control-allow-credentials'] ) && 
			                      $headers['access-control-allow-credentials'] === 'true';
			
			$severity = $allows_credentials ? 'critical' : 'high';
			$threat = $allows_credentials ? 85 : 70;
			
			return array(
				'id'          => 'cors-misconfiguration',
				'title'       => 'Insecure CORS Configuration',
				'description' => sprintf(
					'Your REST API has Access-Control-Allow-Origin set to wildcard (*). %s Restrict CORS to specific trusted domains.',
					$allows_credentials ? 'Combined with credentials=true, this allows ANY site to steal authenticated data.' : 'This allows any website to access your API.'
				),
				'severity'    => $severity,
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-cors-configuration/',
				'training_link' => 'https://wpshadow.com/training/cors-security/',
				'auto_fixable' => true,
				'threat_level' => $threat,
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
	}}

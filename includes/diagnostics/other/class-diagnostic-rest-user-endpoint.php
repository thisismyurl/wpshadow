<?php
declare(strict_types=1);
/**
 * REST API User Endpoint Diagnostic
 *
 * Philosophy: Information disclosure - prevent user data leakage
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if REST API user endpoint exposes user data.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_REST_User_Endpoint extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test REST API user endpoint
		$rest_url = rest_url( 'wp/v2/users' );
		$response = wp_remote_get(
			$rest_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( $status === 200 ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( ! empty( $data ) && is_array( $data ) ) {
				return array(
					'id'            => 'rest-user-endpoint',
					'title'         => 'REST API Exposes User Data',
					'description'   => sprintf(
						'The /wp-json/wp/v2/users endpoint is publicly accessible and exposes %d user(s) information including usernames, emails, and IDs. Restrict access to authenticated requests.',
						count( $data )
					),
					'severity'      => 'medium',
					'category'      => 'security',
					'kb_link'       => 'https://wpshadow.com/kb/secure-rest-api-users/',
					'training_link' => 'https://wpshadow.com/training/rest-api-security/',
					'auto_fixable'  => true,
					'threat_level'  => 65,
				);
			}
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

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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: REST User Endpoint
	 * Slug: -rest-user-endpoint
	 * File: class-diagnostic-rest-user-endpoint.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: REST User Endpoint
	 * Slug: -rest-user-endpoint
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__rest_user_endpoint(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}

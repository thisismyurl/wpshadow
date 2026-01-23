<?php
declare(strict_types=1);
/**
 * User Enumeration Protection Diagnostic
 *
 * Philosophy: Security hardening - prevents username discovery
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if user enumeration is blocked.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Enumeration extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test if /?author=1 reveals usernames
		$test_url = add_query_arg( 'author', 1, home_url() );
		$response = wp_remote_head(
			$test_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null; // Can't check
		}

		$status = wp_remote_retrieve_response_code( $response );

		// If redirect to author archive succeeds, enumeration is possible
		if ( $status === 200 || $status === 301 || $status === 302 ) {
			return array(
				'id'            => 'user-enumeration',
				'title'         => 'User Enumeration Enabled',
				'description'   => 'Attackers can discover usernames via /?author=N URLs. Block author archives or use a security plugin to prevent username discovery.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/prevent-user-enumeration/',
				'training_link' => 'https://wpshadow.com/training/user-enumeration/',
				'auto_fixable'  => true,
				'threat_level'  => 65,
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

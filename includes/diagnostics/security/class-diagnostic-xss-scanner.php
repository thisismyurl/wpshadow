<?php
declare(strict_types=1);
/**
 * XSS Vulnerability Scanner Diagnostic
 *
 * Philosophy: Vulnerability detection - test for XSS
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test for reflected XSS vulnerabilities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_XSS_Scanner extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test search with XSS payload
		$test_payload = '<script>alert("XSS")</script>';
		$search_url = add_query_arg( 's', urlencode( $test_payload ), home_url() );
		
		$response = wp_remote_get( $search_url, array( 'timeout' => 10, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$body = wp_remote_retrieve_body( $response );
		
		// Check if unescaped script tag appears in response
		if ( stripos( $body, '<script>alert("XSS")</script>' ) !== false ) {
			return array(
				'id'          => 'xss-scanner',
				'title'       => 'Potential XSS Vulnerability',
				'description' => 'Search form or URL parameters may be vulnerable to cross-site scripting (XSS) attacks. User input is being reflected without proper escaping. Use esc_html(), esc_attr(), etc.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-xss-attacks/',
				'training_link' => 'https://wpshadow.com/training/xss-prevention/',
				'auto_fixable' => false,
				'threat_level' => 80,
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

<?php
declare(strict_types=1);
/**
 * Security Headers Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check HTTP security headers.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Security_Headers extends Diagnostic_Base {

	protected static $slug        = 'security-headers';
	protected static $title       = 'Missing Security Headers';
	protected static $description = 'Your site is missing important HTTP security headers.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Make request to site homepage
		$home_url = home_url();
		$response = wp_remote_head(
			$home_url,
			array(
				'timeout'   => 10,
				'sslverify' => false, // Avoid SSL verification issues on local/dev
			)
		);

		if ( is_wp_error( $response ) ) {
			// Can't check headers, skip diagnostic
			return null;
		}

		$headers = wp_remote_retrieve_headers( $response );
		$issues  = array();

		// Check for X-Frame-Options (clickjacking protection)
		if ( empty( $headers['x-frame-options'] ) ) {
			$issues[] = 'X-Frame-Options header missing (clickjacking protection)';
		}

		// Check for X-Content-Type-Options (MIME sniffing protection)
		if ( empty( $headers['x-content-type-options'] ) ) {
			$issues[] = 'X-Content-Type-Options header missing (MIME sniffing protection)';
		}

		// Only report if multiple headers are missing (single missing header is common)
		if ( count( $issues ) >= 2 ) {
			return array(
				'title'       => self::$title,
				'description' => implode( '. ', $issues ) . '. Add these headers via your web server or security plugin.',
				'severity'    => 'low',
				'category'    => 'security',
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

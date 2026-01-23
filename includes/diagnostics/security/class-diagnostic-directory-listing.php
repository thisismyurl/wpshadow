<?php
declare(strict_types=1);
/**
 * Directory Listing Security Diagnostic
 *
 * Philosophy: Security hardening - prevent file enumeration
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if directory listing is disabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Directory_Listing extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test uploads directory
		$upload_dir = wp_upload_dir();
		$test_url = trailingslashit( $upload_dir['baseurl'] );
		
		$response = wp_remote_head( $test_url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null; // Can't check
		}
		
		$body = wp_remote_retrieve_body( wp_remote_get( $test_url, array( 'timeout' => 5, 'sslverify' => false ) ) );
		
		// Check for directory listing indicators
		if ( strpos( $body, 'Index of' ) !== false || strpos( $body, 'Parent Directory' ) !== false ) {
			return array(
				'id'          => 'directory-listing',
				'title'       => 'Directory Listing Enabled',
				'description' => 'Your uploads directory allows file listing, exposing your file structure. Disable directory indexes via .htaccess or server config.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/disable-directory-listing/',
				'training_link' => 'https://wpshadow.com/training/directory-listing/',
				'auto_fixable' => true,
				'threat_level' => 60,
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

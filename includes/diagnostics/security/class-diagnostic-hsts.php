<?php
declare(strict_types=1);
/**
 * HTTP Strict Transport Security (HSTS) Diagnostic
 *
 * Philosophy: Security hardening - prevent SSL stripping attacks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if HSTS header is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_HSTS extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Only check if site uses HTTPS
		if ( ! is_ssl() ) {
			return null;
		}
		
		$response = wp_remote_head( home_url(), array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$headers = wp_remote_retrieve_headers( $response );
		
		if ( empty( $headers['strict-transport-security'] ) ) {
			return array(
				'id'          => 'hsts-header',
				'title'       => 'HSTS Header Not Configured',
				'description' => 'Your HTTPS site lacks HTTP Strict Transport Security (HSTS) header, making it vulnerable to SSL stripping attacks. Add the Strict-Transport-Security header.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-hsts/',
				'training_link' => 'https://wpshadow.com/training/hsts-security/',
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

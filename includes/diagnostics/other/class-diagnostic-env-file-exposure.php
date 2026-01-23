<?php
declare(strict_types=1);
/**
 * Environment File Exposure Diagnostic
 *
 * Philosophy: Credential protection - hide .env files
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if .env files are web-accessible.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_ENV_File_Exposure extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test if .env is accessible
		$env_url  = trailingslashit( home_url() ) . '.env';
		$response = wp_remote_get(
			$env_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		// If .env is accessible and contains environment variables
		if ( $status_code === 200 &&
			( strpos( $body, 'DB_PASSWORD' ) !== false ||
				strpos( $body, 'API_KEY' ) !== false ||
				strpos( $body, '=' ) !== false ) ) {

			return array(
				'id'            => 'env-file-exposure',
				'title'         => 'Environment File Publicly Accessible',
				'description'   => 'Your .env file is accessible via web browser, exposing database credentials, API keys, and other secrets. Block access to .env files immediately via .htaccess or server configuration.',
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/protect-env-files/',
				'training_link' => 'https://wpshadow.com/training/environment-security/',
				'auto_fixable'  => true,
				'threat_level'  => 80,
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

<?php
declare(strict_types=1);
/**
 * API Key Storage Diagnostic
 *
 * Philosophy: Secret management - use constants not database
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if API keys are stored in database.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_API_Key_Storage extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Search wp_options for common API key patterns
		$api_patterns = array( '%api_key%', '%api_secret%', '%access_token%', '%secret_key%' );
		$found_keys = array();
		
		foreach ( $api_patterns as $pattern ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE '%transient%' LIMIT 5",
					$pattern
				)
			);
			
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					// Exclude WordPress core keys
					if ( ! in_array( $result->option_name, array( 'auth_key', 'secure_auth_key', 'logged_in_key', 'nonce_key' ), true ) ) {
						$found_keys[] = $result->option_name;
					}
				}
			}
		}
		
		if ( ! empty( $found_keys ) ) {
			return array(
				'id'          => 'api-key-storage',
				'title'       => 'API Keys Stored in Database',
				'description' => sprintf(
					'API keys found in wp_options: %s. Database-stored secrets are exposed via SQL injection or database dumps. Move to constants in wp-config.php outside version control.',
					implode( ', ', array_slice( $found_keys, 0, 3 ) )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-api-key-storage/',
				'training_link' => 'https://wpshadow.com/training/secret-management/',
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

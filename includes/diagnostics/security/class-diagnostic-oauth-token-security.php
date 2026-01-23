<?php
declare(strict_types=1);
/**
 * OAuth Token Security Diagnostic
 *
 * Philosophy: Third-party auth - secure token handling
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for secure OAuth token storage.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_OAuth_Token_Security extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check for OAuth tokens in user meta or options (should be encrypted)
		$results = $wpdb->get_results(
			"SELECT COUNT(*) as count FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' OR meta_key LIKE '%token%'"
		);
		
		if ( ! empty( $results[0]->count ) && $results[0]->count > 0 ) {
			// Tokens found - check if encrypted
			$tokens = $wpdb->get_results(
				"SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' LIMIT 1"
			);
			
			if ( ! empty( $tokens ) ) {
				$token_value = $tokens[0]->meta_value;
				
				// Check if it looks encrypted
				if ( ! preg_match( '/^[a-f0-9]+$/', $token_value ) && strlen( $token_value ) > 100 ) {
					return array(
						'id'          => 'oauth-token-security',
						'title'       => 'OAuth Tokens May Not Be Encrypted',
						'description' => 'OAuth tokens stored in database without encryption. Compromised database exposes third-party accounts. Encrypt sensitive tokens at rest.',
						'severity'    => 'high',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/encrypt-oauth-tokens/',
						'training_link' => 'https://wpshadow.com/training/token-security/',
						'auto_fixable' => false,
						'threat_level' => 75,
					);
				}
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

<?php
declare(strict_types=1);
/**
 * Exposed .git Directory Diagnostic
 *
 * Philosophy: Source control security - protect repository data
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if .git directory is web-accessible.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Git_Directory_Exposed extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test if .git/config is accessible
		$git_config_url = trailingslashit( home_url() ) . '.git/config';
		$response = wp_remote_get( $git_config_url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$status = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		
		// Check if .git directory is accessible
		if ( $status === 200 && 
		     ( strpos( $body, '[core]' ) !== false || 
		       strpos( $body, '[remote' ) !== false ) ) {
			
			return array(
				'id'          => 'git-directory-exposed',
				'title'       => '.git Directory Publicly Accessible',
				'description' => 'Your .git directory is accessible via web browser, exposing complete source code history including deleted files, credentials in old commits, and development secrets. Block .git access via .htaccess immediately.',
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/protect-git-directory/',
				'training_link' => 'https://wpshadow.com/training/source-control-security/',
				'auto_fixable' => true,
				'threat_level' => 90,
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

<?php
declare(strict_types=1);
/**
 * MySQL LOAD_FILE Privileges Diagnostic
 *
 * Philosophy: Database security - prevent file system access
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if database user has FILE privilege.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_MySQL_Load_File extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check user privileges
		$grants = $wpdb->get_results( "SHOW GRANTS FOR CURRENT_USER", ARRAY_N );
		
		if ( empty( $grants ) ) {
			return null;
		}
		
		foreach ( $grants as $grant ) {
			$grant_text = strtoupper( $grant[0] );
			
			// Check for FILE privilege
			if ( strpos( $grant_text, 'FILE' ) !== false ) {
				return array(
					'id'          => 'mysql-load-file',
					'title'       => 'Database User Has FILE Privilege',
					'description' => 'Your database user has FILE privilege, allowing LOAD_FILE() to read any file the MySQL server can access (including /etc/passwd). SQL injection becomes much more dangerous. Remove FILE privilege immediately.',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/revoke-file-privilege/',
					'training_link' => 'https://wpshadow.com/training/database-privileges/',
					'auto_fixable' => false,
					'threat_level' => 80,
				);
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

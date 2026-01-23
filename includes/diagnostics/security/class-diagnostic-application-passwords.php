<?php
declare(strict_types=1);
/**
 * Application Passwords Security Diagnostic
 *
 * Philosophy: Security awareness - manage WP 5.6+ app passwords
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for application passwords usage.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Application_Passwords extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Application passwords introduced in WP 5.6
		if ( ! class_exists( 'WP_Application_Passwords' ) ) {
			return null;
		}
		
		// Check if any users have application passwords
		global $wpdb;
		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_application_passwords'"
		);
		
		if ( $count > 0 ) {
			return array(
				'id'          => 'application-passwords',
				'title'       => 'Application Passwords in Use',
				'description' => sprintf(
					'%d user(s) have application passwords enabled. Review and revoke unused app passwords to minimize attack vectors.',
					$count
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/manage-application-passwords/',
				'training_link' => 'https://wpshadow.com/training/application-passwords/',
				'auto_fixable' => false,
				'threat_level' => 55,
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

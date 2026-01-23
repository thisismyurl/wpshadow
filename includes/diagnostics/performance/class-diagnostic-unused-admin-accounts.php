<?php
declare(strict_types=1);
/**
 * Unused Admin Accounts Diagnostic
 *
 * Philosophy: Security hardening - reduce attack surface
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for inactive admin accounts.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Unused_Admin_Accounts extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Get all administrators
		$admins = get_users( array( 'role' => 'administrator' ) );
		
		$inactive_admins = array();
		$ninety_days_ago = time() - ( 90 * DAY_IN_SECONDS );
		
		foreach ( $admins as $admin ) {
			// Check last login (if tracked) or user_registered
			$last_login = get_user_meta( $admin->ID, 'last_login', true );
			
			if ( empty( $last_login ) ) {
				// Fall back to registration date
				$registered = strtotime( $admin->user_registered );
				if ( $registered < $ninety_days_ago ) {
					$inactive_admins[] = $admin->user_login;
				}
			} elseif ( $last_login < $ninety_days_ago ) {
				$inactive_admins[] = $admin->user_login;
			}
		}
		
		if ( ! empty( $inactive_admins ) ) {
			return array(
				'id'          => 'unused-admin-accounts',
				'title'       => 'Inactive Admin Accounts Detected',
				'description' => sprintf(
					'%d admin account(s) have not logged in for 90+ days: %s. Remove or demote unused admin accounts to reduce attack surface.',
					count( $inactive_admins ),
					implode( ', ', array_slice( $inactive_admins, 0, 3 ) )
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/audit-admin-accounts/',
				'training_link' => 'https://wpshadow.com/training/admin-account-hygiene/',
				'auto_fixable' => false,
				'threat_level' => 65,
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

<?php
declare(strict_types=1);
/**
 * User Role Capabilities Audit Diagnostic
 *
 * Philosophy: Privilege escalation prevention - audit user capabilities
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for users with unexpected capabilities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Role_Audit extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Get all users with elevated roles
		$users            = get_users( array( 'role__in' => array( 'editor', 'author' ) ) );
		$suspicious_users = array();

		// Dangerous capabilities that shouldn't be on non-admin roles
		$dangerous_caps = array( 'delete_users', 'create_users', 'manage_options', 'activate_plugins' );

		foreach ( $users as $user ) {
			foreach ( $dangerous_caps as $cap ) {
				if ( $user->has_cap( $cap ) ) {
					$suspicious_users[] = sprintf( '%s (%s)', $user->user_login, $cap );
					break;
				}
			}
		}

		if ( ! empty( $suspicious_users ) ) {
			return array(
				'id'            => 'user-role-audit',
				'title'         => 'Users With Elevated Capabilities',
				'description'   => sprintf(
					'Non-admin users have dangerous capabilities: %s. Review and remove unnecessary capabilities to prevent privilege escalation.',
					implode( ', ', array_slice( $suspicious_users, 0, 3 ) )
				),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/audit-user-capabilities/',
				'training_link' => 'https://wpshadow.com/training/user-role-security/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
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

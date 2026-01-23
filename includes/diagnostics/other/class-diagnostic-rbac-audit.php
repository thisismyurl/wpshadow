<?php
declare(strict_types=1);
/**
 * Role-Based Access Control Audit Diagnostic
 *
 * Philosophy: Access control - verify proper role separation
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for proper role-based access control.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_RBAC_Audit extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_roles;

		// Check for custom roles with excessive permissions
		if ( empty( $wp_roles ) ) {
			return null;
		}

		$suspicious_roles = array();

		foreach ( $wp_roles->roles as $role_name => $role_data ) {
			if ( ! in_array( $role_name, array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ), true ) ) {
				// Custom role - check for excessive permissions
				if ( isset( $role_data['capabilities']['manage_options'] ) ) {
					$suspicious_roles[] = $role_name;
				}
			}
		}

		if ( ! empty( $suspicious_roles ) ) {
			return array(
				'id'            => 'rbac-audit',
				'title'         => 'Custom Roles with Admin Capabilities',
				'description'   => sprintf(
					'Found custom roles with administrative permissions: %s. This may indicate privilege escalation. Remove excessive permissions from non-admin roles.',
					implode( ', ', $suspicious_roles )
				),
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/audit-user-roles/',
				'training_link' => 'https://wpshadow.com/training/role-management/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
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

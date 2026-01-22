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
}

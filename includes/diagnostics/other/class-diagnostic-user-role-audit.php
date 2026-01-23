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

}
<?php
declare(strict_types=1);
/**
 * Unused Admin Accounts Diagnostic
 *
 * Philosophy: Security hardening - reduce attack surface
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for inactive admin accounts.
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
}

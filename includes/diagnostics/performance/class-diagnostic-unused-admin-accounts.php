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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Unused Admin Accounts
	 * Slug: -unused-admin-accounts
	 * File: class-diagnostic-unused-admin-accounts.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Unused Admin Accounts
	 * Slug: -unused-admin-accounts
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__unused_admin_accounts(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}

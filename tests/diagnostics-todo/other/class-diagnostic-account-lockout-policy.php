<?php
declare(strict_types=1);
/**
 * Account Lockout Policy Diagnostic
 *
 * Philosophy: Brute force protection - lock out after failed attempts
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if account lockout policy is enforced.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Account_Lockout_Policy extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$lockout_plugins = array(
			'wordfence/wordfence.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);

		$active = get_option( 'active_plugins', array() );
		foreach ( $lockout_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}

		return array(
			'id'            => 'account-lockout-policy',
			'title'         => 'No Account Lockout After Failed Attempts',
			'description'   => 'No login lockout mechanism. Attackers can attempt unlimited password guesses. Implement account lockout (5+ failed attempts = 30min lockout).',
			'severity'      => 'high',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/enable-account-lockout/',
			'training_link' => 'https://wpshadow.com/training/brute-force-protection/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Account Lockout Policy
	 * Slug: -account-lockout-policy
	 * File: class-diagnostic-account-lockout-policy.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Account Lockout Policy
	 * Slug: -account-lockout-policy
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
	public static function test_live__account_lockout_policy(): array {
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

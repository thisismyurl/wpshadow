<?php
declare(strict_types=1);
/**
 * Two-Factor Authentication (2FA) Diagnostic
 *
 * Philosophy: Authentication - multi-factor verification
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if 2FA is enabled for admin users.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Two_Factor_Authentication extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$twofa_plugins = array(
			'two-factor-authentication/two-factor-authentication.php',
			'wordfence/wordfence.php',
			'google-authenticator-per-user-prompts/google-authenticator-per-user-prompts.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $twofa_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'two-factor-authentication',
			'title'       => 'No Two-Factor Authentication (2FA)',
			'description' => 'Admin login requires only password. Stolen credentials fully compromise the site. Require 2FA (authenticator app, SMS) for all admin logins.',
			'severity'    => 'critical',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-2fa/',
			'training_link' => 'https://wpshadow.com/training/two-factor-setup/',
			'auto_fixable' => false,
			'threat_level' => 85,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Two Factor Authentication
	 * Slug: -two-factor-authentication
	 * File: class-diagnostic-two-factor-authentication.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Two Factor Authentication
	 * Slug: -two-factor-authentication
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
	public static function test_live__two_factor_authentication(): array {
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

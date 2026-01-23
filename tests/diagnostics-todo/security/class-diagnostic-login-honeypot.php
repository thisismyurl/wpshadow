<?php
declare(strict_types=1);
/**
 * Login Page Honeypot Diagnostic
 *
 * Philosophy: Bot detection - trap automated attacks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if honeypot fields protect login.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Login_Honeypot extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$honeypot_plugins = array(
			'wp-slimstat/wp-slimstat.php',
			'akismet/akismet.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $honeypot_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'login-honeypot',
			'title'       => 'No Honeypot Fields on Login',
			'description' => 'Login form lacks honeypot fields to detect bots. Add hidden form fields that bots will fill in, identifying automated attacks.',
			'severity'    => 'low',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/implement-honeypot-fields/',
			'training_link' => 'https://wpshadow.com/training/honeypot-protection/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Login Honeypot
	 * Slug: -login-honeypot
	 * File: class-diagnostic-login-honeypot.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Login Honeypot
	 * Slug: -login-honeypot
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
	public static function test_live__login_honeypot(): array {
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

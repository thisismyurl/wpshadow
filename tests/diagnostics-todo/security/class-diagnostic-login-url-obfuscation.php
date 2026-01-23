<?php
declare(strict_types=1);
/**
 * Login URL Obfuscation Diagnostic
 *
 * Philosophy: Security through obscurity layer - reduces automated attacks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if default login URLs are changed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Login_URL_Obfuscation extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for login URL change plugins
		$login_plugins = array(
			'wps-hide-login/wps-hide-login.php',
			'rename-wp-login/rename-wp-login.php',
			'hide-login-page/hide-login-page.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $login_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Login URL likely changed
			}
		}
		
		return array(
			'id'          => 'login-url-obfuscation',
			'title'       => 'Default Login URL Exposed',
			'description' => 'Your site uses the default /wp-login.php and /wp-admin URLs, making it easier for automated bots to find and attack. Consider changing your login URL.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/change-wordpress-login-url/',
			'training_link' => 'https://wpshadow.com/training/login-url-security/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Login URL Obfuscation
	 * Slug: -login-url-obfuscation
	 * File: class-diagnostic-login-url-obfuscation.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Login URL Obfuscation
	 * Slug: -login-url-obfuscation
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
	public static function test_live__login_url_obfuscation(): array {
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

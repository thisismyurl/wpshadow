<?php
declare(strict_types=1);
/**
 * Hotlink Protection Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if basic hotlink protection is enabled for media assets.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Hotlink_Protection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::is_apache_like() ) {
			return null; // Only evaluate on Apache-like setups.
		}
		
		if ( ! self::has_hotlink_rules() ) {
			return array(
				'id'           => 'hotlink-protection-missing',
				'title'        => 'Hotlink Protection Not Enabled',
				'description'  => 'Blocking image hotlinking saves bandwidth and prevents unauthorized re-use of your media.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/enable-hotlink-protection/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=hotlink-protection',
				'auto_fixable' => true,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
	
	/**
	 * Determine if server is Apache-like and supports .htaccess rules.
	 *
	 * @return bool
	 */
	private static function is_apache_like() {
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();
			return in_array( 'mod_rewrite', $modules, true );
		}
		
		return ( isset( $_SERVER['SERVER_SOFTWARE'] ) && false !== stripos( $_SERVER['SERVER_SOFTWARE'], 'apache' ) );
	}
	
	/**
	 * Check if the WPShadow hotlink protection block exists in .htaccess.
	 *
	 * @return bool
	 */
	private static function has_hotlink_rules() {
		$htaccess = ABSPATH . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			return false;
		}
		
		$contents = file_get_contents( $htaccess );
		return false !== strpos( $contents, '# BEGIN WPShadow Hotlink Protection' );
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Hotlink Protection
	 * Slug: -hotlink-protection
	 * File: class-diagnostic-hotlink-protection.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Hotlink Protection
	 * Slug: -hotlink-protection
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
	public static function test_live__hotlink_protection(): array {
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

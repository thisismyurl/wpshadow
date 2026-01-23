<?php
declare(strict_types=1);
/**
 * Debug Mode Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if debug mode is enabled on live site.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Debug_Mode extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				'id'           => 'debug-mode-enabled',
				'title'        => 'Debug Mode Enabled',
				'description'  => 'WordPress debug mode is active. Disable it on live sites for better security.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/disable-wordpress-debug-mode/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=debug-mode',
				'auto_fixable' => self::can_modify_wp_config(),
				'threat_level' => 70,
			);
		}
		
		return null;
	}
	
	/**
	 * Check if we can modify wp-config.php.
	 *
	 * @return bool True if wp-config.php is writable.
	 */
	private static function can_modify_wp_config() {
		$config_file = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_file ) ) {
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
		}
		return file_exists( $config_file ) && is_writable( $config_file );
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Debug Mode
	 * Slug: -debug-mode
	 * File: class-diagnostic-debug-mode.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Debug Mode
	 * Slug: -debug-mode
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
	public static function test_live__debug_mode(): array {
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

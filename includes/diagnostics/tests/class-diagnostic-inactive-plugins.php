<?php
declare(strict_types=1);
/**
 * Inactive Plugins Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for inactive plugins that can be cleaned up.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Inactive_Plugins extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$inactive = self::get_inactive_plugins();
		$count    = count( $inactive );
		
		if ( $count > 0 ) {
			return array(
				'id'           => 'inactive-plugins',
				'title'        => "{$count} Inactive Plugin" . ( $count !== 1 ? 's' : '' ) . ' Installed',
				'description'  => 'Inactive plugins add bloat and potential attack surface. Remove ones you no longer need.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/how-to-clean-up-inactive-plugins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-cleanup',
				'action_link'  => admin_url( 'plugins.php' ),
				'action_text'  => 'Review Plugins',
				'auto_fixable' => true,
				'threat_level' => 50,
			);
		}
		
		return null;
	}
	
	/**
	 * Get inactive plugin file paths.
	 *
	 * @return array List of plugin basenames that are inactive.
	 */
	private static function get_inactive_plugins() {
		$all_plugins   = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );
		
		return array_values( array_diff( $all_plugins, $active_plugins ) );
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Inactive Plugins
	 * Slug: -inactive-plugins
	 * File: class-diagnostic-inactive-plugins.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Inactive Plugins
	 * Slug: -inactive-plugins
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
	public static function test_live__inactive_plugins(): array {
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

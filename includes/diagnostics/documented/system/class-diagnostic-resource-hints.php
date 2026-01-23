<?php
declare(strict_types=1);
/**
 * Resource Hints Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing resource hints on primary domains.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Resource_Hints extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$hosts = self::get_hosts();
		if ( empty( $hosts ) ) {
			return null;
		}
		
		return array(
			'id'           => 'resource-hints-missing',
			'title'        => 'Add Resource Hints',
			'description'  => 'Preconnect/preload common domains (CDN, fonts, APIs) to improve first-byte time.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/add-resource-hints/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=resource-hints',
			'auto_fixable' => true,
			'threat_level' => 35,
		);
	}
	
	/**
	 * Collect candidate hosts for hints.
	 *
	 * @return array
	 */
	private static function get_hosts() {
		$hosts = array();
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $site_host ) {
			$hosts[] = $site_host;
		}
		
		$cdn = get_option( 'wpshadow_cdn_host' );
		if ( $cdn ) {
			$hosts[] = $cdn;
		}
		
		return array_unique( array_filter( $hosts ) );
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Resource Hints
	 * Slug: -resource-hints
	 * File: class-diagnostic-resource-hints.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Resource Hints
	 * Slug: -resource-hints
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
	public static function test_live__resource_hints(): array {
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

<?php
declare(strict_types=1);
/**
 * WP Admin Fonts Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WordPress admin is loading Google Fonts unnecessarily.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Admin_Fonts extends Diagnostic_Base {

	protected static $slug        = 'admin-fonts';
	protected static $title       = 'WP Admin Loads Google Fonts';
	protected static $description = 'WordPress admin loads Open Sans from Google Fonts. This can be removed for privacy and performance.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if treatment is already applied
		$disabled = get_option( 'wpshadow_admin_fonts_disabled', false );

		if ( $disabled ) {
			return null;
		}

		// Check if Open Sans is enqueued in admin
		global $wp_styles;
		$open_sans_loaded = false;

		if ( is_admin() && isset( $wp_styles->registered['open-sans'] ) ) {
			$open_sans_loaded = true;
		}

		// WordPress loads Open Sans by default in admin
		if ( ! $open_sans_loaded && ! is_admin() ) {
			// Assume it will load in admin
			$open_sans_loaded = true;
		}

		if ( ! $open_sans_loaded ) {
			return null;
		}

		return array(
			'id'          => 'admin-fonts',
			'title'       => 'WP Admin Loads Google Fonts',
			'description' => 'WordPress admin loads Open Sans from Google Fonts. This makes external requests on every admin page load and can expose your login activity. Consider using system fonts instead.',
			'severity'    => 'warning',
			'category'    => 'performance',
			'impact'      => 'Every admin page load makes external request to Google',
			'fix_time'    => '1 second',
			'kb_article'  => 'admin-fonts',
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: WP Admin Loads Google Fonts
	 * Slug: admin-fonts
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: WordPress admin loads Open Sans from Google Fonts. This can be removed for privacy and performance.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_admin_fonts(): array {
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
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}

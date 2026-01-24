<?php
declare(strict_types=1);
/**
 * Head Cleanup - WordPress Shortlink Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WordPress shortlink is enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-emoji, head-cleanup-oembed, head-cleanup-rsd
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup_Shortlink extends Diagnostic_Base {

	protected static $slug         = 'head-cleanup-shortlink';
	protected static $title        = 'WordPress Shortlink';
	protected static $description  = 'Checks if WordPress shortlink functionality is enabled and can be removed.';
	protected static $family       = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_shortlink_enabled() ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The WordPress shortlink feature is rarely used in modern sites. Removing it reduces page headers and improves performance.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 10,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if shortlink is enabled
	 *
	 * @return bool
	 */
	private static function is_shortlink_enabled(): bool {
		return has_action( 'wp_head', 'wp_shortlink_wp_head' ) !== false;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: WordPress Shortlink
	 * Slug: head-cleanup-shortlink
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if WordPress shortlink functionality is enabled and can be removed.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_head_cleanup_shortlink(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// Pattern: has_action() returns false when action NOT hooked (healthy)
		// Pattern: has_action() returns integer when action IS hooked (issue found)
		// So: NULL result = healthy, array result = issue detected
		
		if ($result === null) {
			return [
				'passed' => true,
				'message' => 'WordPress shortlink functionality is properly disabled (healthy)',
			];
		}
		
		return [
			'passed' => false,
			'message' => 'WordPress shortlink functionality is enabled and could be removed',
		];
	}

}

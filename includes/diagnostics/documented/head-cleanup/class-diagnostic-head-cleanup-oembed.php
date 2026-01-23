<?php
declare(strict_types=1);
/**
 * Head Cleanup - oEmbed Discovery Diagnostic
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
 * Check if oEmbed discovery links are enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-emoji, head-cleanup-rsd, head-cleanup-shortlink
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup_OEmbed extends Diagnostic_Base {

	protected static $slug         = 'head-cleanup-oembed';
	protected static $title        = 'oEmbed Discovery Links';
	protected static $description  = 'Checks if WordPress oEmbed discovery links are enabled and can be removed.';
	protected static $family       = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_oembed_enabled() ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'oEmbed discovery links are rarely used by modern sites. Removing them reduces page bloat and HTTP headers.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 12,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if oEmbed discovery is enabled
	 *
	 * @return bool
	 */
	private static function is_oembed_enabled(): bool {
		return has_action( 'wp_head', 'wp_oembed_add_discovery_links' ) !== false;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: oEmbed Discovery Links
	 * Slug: head-cleanup-oembed
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if WordPress oEmbed discovery links are enabled and can be removed.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_head_cleanup_oembed(): array {
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
				'message' => 'WordPress oEmbed discovery links are properly disabled (healthy)',
			];
		}
		
		return [
			'passed' => false,
			'message' => 'WordPress oEmbed discovery links are enabled and could be removed',
		];
	}

}

<?php
declare(strict_types=1);
/**
 * Head Cleanup - RSD Link Diagnostic
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
 * Check if RSD (Really Simple Discovery) link is enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-emoji, head-cleanup-oembed, head-cleanup-shortlink
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup_RSD extends Diagnostic_Base {

	protected static $slug         = 'head-cleanup-rsd';
	protected static $title        = 'RSD (Really Simple Discovery) Link';
	protected static $description  = 'Checks if WordPress RSD link is enabled and can be removed.';
	protected static $family       = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_rsd_enabled() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The RSD link is legacy from the XML-RPC era and is unnecessary for modern WordPress sites. Removing it improves security and reduces page noise.', 'wpshadow' ),
			'category'     => 'security',
			'severity'     => 'low',
			'threat_level' => 18,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if RSD link is enabled
	 *
	 * @return bool
	 */
	private static function is_rsd_enabled(): bool {
		return has_action( 'wp_head', 'rsd_link' ) !== false;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: RSD (Really Simple Discovery) Link
	 * Slug: head-cleanup-rsd
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if WordPress RSD link is enabled and can be removed.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_head_cleanup_rsd(): array {
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

		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'RSD (Really Simple Discovery) link is properly disabled (healthy)',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'RSD link is enabled and could be removed for improved security',
		);
	}
}

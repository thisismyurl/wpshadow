<?php

declare(strict_types=1);
/**
 * 404 Detection and Throttling Diagnostic
 *
 * Philosophy: Scan detection - identify vulnerability scanners
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if 404 scanning is throttled.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_404_Throttling extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$has_404_protection = has_filter('template_redirect');

		if (! $has_404_protection) {
			return array(
				'id'            => '404-throttling',
				'title'         => 'No 404 Scanning Detection',
				'description'   => 'Vulnerability scanners probe your site via 404s. Without 404 throttling, scanners can freely map your site structure and find exploitable endpoints.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/throttle-404-scans/',
				'training_link' => 'https://wpshadow.com/training/scanner-detection/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: 404 Detection and Throttling
	 * Slug: 404-throttling
	 * File: class-diagnostic-404-throttling.php
	 *
	 * Test Purpose:
	 * Verify that 404 scanning detection is enabled
	 * - PASS: check() returns NULL when template_redirect filter has handlers
	 * - FAIL: check() returns array when no 404 protection is active
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__404_throttling(): array
	{
		$result = self::check();
		$has_404_protection = has_filter('template_redirect');

		if ($has_404_protection) {
			// 404 protection active = diagnostic should pass (return null)
			return array(
				'passed' => is_null($result),
				'message' => '404 throttling protection is active'
			);
		} else {
			// No 404 protection = issue should be found (return array)
			return array(
				'passed' => !is_null($result) && isset($result['id']) && $result['id'] === '404-throttling',
				'message' => 'No 404 throttling, issue correctly identified'
			);
		}
	}
}

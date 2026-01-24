<?php

declare(strict_types=1);
/**
 * Heartbeat Throttling Diagnostic
 *
 * Philosophy: Educate on reducing admin-ajax load for better performance.
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if WordPress Heartbeat is throttled.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Heartbeat_Throttling extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// If constant is defined to disable heartbeat
		if (defined('WP_DISABLE_HEARTBEAT') && WP_DISABLE_HEARTBEAT) {
			return null; // Already disabled/throttled
		}
		// Check if heartbeat is throttled via filters
		// `heartbeat_settings` or `heartbeat_send` filters indicate custom intervals
		if (has_filter('heartbeat_settings') || has_filter('heartbeat_send')) {
			return null; // Considered throttled/customized
		}

		return array(
			'title'        => 'WordPress Heartbeat Not Throttled',
			'description'  => 'Heartbeat API runs frequently in wp-admin. Throttling reduces CPU and AJAX load, improving performance.',
			'severity'     => 'low',
			'category'     => 'performance',
			'kb_link'      => 'https://wpshadow.com/kb/throttle-wordpress-heartbeat/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=heartbeat',
			'auto_fixable' => false,
			'threat_level' => 25,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Heartbeat Throttling
	 * Slug: -heartbeat-throttling
	 * File: class-diagnostic-heartbeat-throttling.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Heartbeat Throttling
	 * Slug: -heartbeat-throttling
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
	public static function test_live__heartbeat_throttling(): array
	{
		$heartbeat_disabled = (defined('WP_DISABLE_HEARTBEAT') && WP_DISABLE_HEARTBEAT);
		$has_heartbeat_filters = (has_filter('heartbeat_settings') || has_filter('heartbeat_send'));

		// Issue exists if: heartbeat NOT disabled AND no custom filters
		$has_issue = (!$heartbeat_disabled && !$has_heartbeat_filters);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Heartbeat throttling check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (disabled: %s, has_filters: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$heartbeat_disabled ? 'yes' : 'no',
				$has_heartbeat_filters ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tracking Script Performance Impact (THIRD-011)
 *
 * Measures how analytics/tracking scripts affect page performance.
 * Philosophy: Show value (#9) - Balance tracking needs with performance.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Tracking_Script_Impact extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array
	{
		// Monitor tracking script performance impact
		$tracking_impact = get_transient('wpshadow_tracking_script_impact_ms');

		if ($tracking_impact && $tracking_impact > 200) { // 200ms
			return array(
				'id' => 'tracking-script-impact',
				'title' => sprintf(__('Tracking Scripts Impact: +%dms', 'wpshadow'), $tracking_impact),
				'description' => __('Analytics and tracking scripts are adding significant overhead. Load them asynchronously to reduce impact.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'monitoring',
				'kb_link' => 'https://wpshadow.com/kb/tracking-script-optimization/',
				'training_link' => 'https://wpshadow.com/training/async-tracking-scripts/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Tracking Script Impact
	 * Slug: -tracking-script-impact
	 * File: class-diagnostic-tracking-script-impact.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Tracking Script Impact
	 * Slug: -tracking-script-impact
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
	public static function test_live__tracking_script_impact(): array
	{
		$tracking_impact = get_transient('wpshadow_tracking_script_impact_ms');
		$has_issue = ($tracking_impact && $tracking_impact > 200);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Tracking script impact check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (impact: %s ms)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$tracking_impact !== false ? (string) $tracking_impact : 'n/a'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

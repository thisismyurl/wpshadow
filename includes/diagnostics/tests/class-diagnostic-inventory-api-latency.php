<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Inventory/Availability API Latency (COMMERCE-348)
 *
 * Tracks stock/availability API impact on pages.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_InventoryApiLatency extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array
	{
		// Monitor third-party inventory API latency
		$api_latency = get_transient('wpshadow_inventory_api_latency_ms');

		if ($api_latency && $api_latency > 1000) { // 1 second
			return array(
				'id' => 'inventory-api-latency',
				'title' => sprintf(__('Slow Inventory API (%dms)', 'wpshadow'), $api_latency),
				'description' => __('Your inventory/stock API is responding slowly. Consider caching results or using a faster API endpoint.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'monitoring',
				'kb_link' => 'https://wpshadow.com/kb/api-performance-monitoring/',
				'training_link' => 'https://wpshadow.com/training/api-optimization/',
				'auto_fixable' => false,
				'threat_level' => 50,
			);
		}
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: InventoryApiLatency
	 * Slug: -inventory-api-latency
	 * File: class-diagnostic-inventory-api-latency.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: InventoryApiLatency
	 * Slug: -inventory-api-latency
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
	public static function test_live__inventory_api_latency(): array
	{
		$api_latency = get_transient('wpshadow_inventory_api_latency_ms');
		$has_issue  = ($api_latency && $api_latency > 1000);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Inventory API latency check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (latency: %sms)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$api_latency !== false ? (string) $api_latency : 'n/a'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

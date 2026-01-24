<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: REST API Endpoint Performance Profiling (WORDPRESS-006)
 *
 * Profiles WordPress REST API endpoints to identify slow responses.
 * Philosophy: Show value (#9) - Optimize API for headless/mobile apps.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_REST_API_Endpoint_Performance extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array
	{
		// Monitor REST API endpoint performance
		$rest_api_time = get_transient('wpshadow_rest_api_avg_time_ms');

		if ($rest_api_time && $rest_api_time > 500) { // 500ms
			return array(
				'id' => 'rest-api-endpoint-performance',
				'title' => sprintf(__('Slow REST API (%dms average)', 'wpshadow'), $rest_api_time),
				'description' => __('Your REST API endpoints are responding slowly. Profile and optimize the slowest endpoints.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'monitoring',
				'kb_link' => 'https://wpshadow.com/kb/rest-api-optimization/',
				'training_link' => 'https://wpshadow.com/training/api-performance-tuning/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: REST API Endpoint Performance
	 * Slug: -rest-api-endpoint-performance
	 * File: class-diagnostic-rest-api-endpoint-performance.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: REST API Endpoint Performance
	 * Slug: -rest-api-endpoint-performance
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
	public static function test_live__rest_api_endpoint_performance(): array
	{
		$avg_ms = get_transient('wpshadow_rest_api_avg_time_ms');
		$has_issue = ($avg_ms && $avg_ms > 500);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'REST API performance check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (avg %.0fms)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				(float) $avg_ms
			);

		return [
			'passed' => $test_passes,
			'message' => $message,
		];
	}
}

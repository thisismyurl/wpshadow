<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: admin-ajax.php Query Hotspots (WP-309)
 * Profiles slow admin-ajax.php endpoints and heavy queries.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 * KB Link: https://wpshadow.com/kb/admin-ajax-performance
 * Training: https://wpshadow.com/training/admin-ajax-optimization
 */
class Diagnostic_AdminAjaxQueryHotspots extends Diagnostic_Base {

	/**
	 * OPEN QUESTIONS (need product guidance):
	 * - Scope: Are we profiling only admin-ajax.php requests, or also WP REST endpoints called from admin screens?
	 * - Data source: Can we rely on recent query logs/transients (e.g., Query Monitor data) or must detection be static/lightweight?
	 * - Thresholds: What latency / query-count thresholds constitute a warning vs fail? Should we key off 95th percentile per action?
	 * - Actions: Which ajax actions are in scope (core + common plugins) and should we whitelist heartbeat/admin-notices?
	 * - Privacy/perf: Are we allowed to sample live queries (adds overhead) or should we only surface historical slow logs if present?
	 */
	public static function check(): ?array {
		// Pending implementation once scope, thresholds, and data source are defined.
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: AdminAjaxQueryHotspots
	 * Slug: -admin-ajax-query-hotspots
	 * File: class-diagnostic-admin-ajax-query-hotspots.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: AdminAjaxQueryHotspots
	 * Slug: -admin-ajax-query-hotspots
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
	public static function test_live__admin_ajax_query_hotspots(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		return array(
			'passed'  => is_null( $result ),
			'message' => 'Diagnostic not yet implemented; awaiting scope/threshold guidance',
		);
	}
}

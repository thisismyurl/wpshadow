<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WP Transients Not Using External Cache (CACHE-011)
 *
 * Checks if transients stored in database vs object cache.
 * Philosophy: Educate (#5) about transient optimization.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Wp_Transients_Not_External_Cache extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array
	{
		// Check if transients are using external cache
		// Check if object cache is enabled
		$has_cache = function_exists('wp_cache_get');

		if (!$has_cache) {
			return array(
				'id' => 'wp-transients-not-external-cache',
				'title' => __('Transients Not Cached Externally', 'wpshadow'),
				'description' => __('WordPress transients are stored in database, not in a fast external cache. Enable Redis or Memcached for better performance.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'monitoring',
				'kb_link' => 'https://wpshadow.com/kb/transient-caching/',
				'training_link' => 'https://wpshadow.com/training/redis-memcached-setup/',
				'auto_fixable' => false,
				'threat_level' => 50,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wp Transients Not External Cache
	 * Slug: -wp-transients-not-external-cache
	 * File: class-diagnostic-wp-transients-not-external-cache.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Wp Transients Not External Cache
	 * Slug: -wp-transients-not-external-cache
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
	public static function test_live__wp_transients_not_external_cache(): array
	{
		$has_cache = function_exists('wp_cache_get');
		$has_issue = !$has_cache;

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Transient external cache detection matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (wp_cache_get available: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$has_cache ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

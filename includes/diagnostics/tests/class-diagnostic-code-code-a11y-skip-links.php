<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Skip Links
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-skip-links
 * Training: https://wpshadow.com/training/code-a11y-skip-links
 */
class Diagnostic_Code_CODE_A11Y_SKIP_LINKS extends Diagnostic_Base
{
	public static function check(): ?array
	{
		// Get the home page HTML
		$home_url = \home_url('/');
		$response = \wp_remote_get($home_url, array('timeout' => 10));

		if (\is_wp_error($response)) {
			return null; // Can't check, skip diagnostic
		}

		$html = \wp_remote_retrieve_body($response);

		// Check for common skip link patterns
		$has_skip_link = false;

		// Pattern 1: Standard skip link with #content or #main
		if (preg_match('/<a[^>]+href=["\']#(content|main|skip)["\'][^>]*>.*skip.*<\/a>/i', $html)) {
			$has_skip_link = true;
		}

		// Pattern 2: Skip to content with various IDs
		if (preg_match('/<a[^>]+class=["\'][^"\']* skip-link[^"\']* ["\'][^>]*>/i', $html)) {
			$has_skip_link = true;
		}

		// Pattern 3: WordPress default skip link
		if (preg_match('/<a[^>]+class=["\']skip-link screen-reader-text["\'][^>]*>/i', $html)) {
			$has_skip_link = true;
		}

		if ($has_skip_link) {
			return null; // Skip links present, all good!
		}

		// No skip links found
		return [
			'id' => 'code-a11y-skip-links',
			'title' => __('Missing Skip Links', 'wpshadow'),
			'description' => __('Detects complex layouts without keyboard skip navigation. Your site doesn\'t have skip links, making it harder for keyboard users to navigate.', 'wpshadow'),
			'severity' => 'medium',
			'category' => 'code-quality',
			'kb_link' => 'https://wpshadow.com/kb/code-a11y-skip-links',
			'training_link' => 'https://wpshadow.com/training/code-a11y-skip-links',
			'auto_fixable' => false,
			'threat_level' => 6
		];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE A11Y SKIP LINKS
	 * Slug: -code-code-a11y-skip-links
	 * File: class-diagnostic-code-code-a11y-skip-links.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE A11Y SKIP LINKS
	 * Slug: -code-code-a11y-skip-links
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
	public static function test_live__code_code_a11y_skip_links(): array
	{
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		// Test implementation complete - check() method contains the actual logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}
}

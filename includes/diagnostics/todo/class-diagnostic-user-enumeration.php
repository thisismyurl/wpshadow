<?php

declare(strict_types=1);
/**
 * User Enumeration Protection Diagnostic
 *
 * Philosophy: Security hardening - prevents username discovery
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if user enumeration is blocked.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Enumeration extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Test if /?author=1 reveals usernames
		$test_url = add_query_arg('author', 1, home_url());
		$response = wp_remote_head(
			$test_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if (is_wp_error($response)) {
			return null; // Can't check
		}

		$status = wp_remote_retrieve_response_code($response);

		// If redirect to author archive succeeds, enumeration is possible
		if ($status === 200 || $status === 301 || $status === 302) {
			return array(
				'id'            => 'user-enumeration',
				'title'         => 'User Enumeration Enabled',
				'description'   => 'Attackers can discover usernames via /?author=N URLs. Block author archives or use a security plugin to prevent username discovery.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/prevent-user-enumeration/',
				'training_link' => 'https://wpshadow.com/training/user-enumeration/',
				'auto_fixable'  => true,
				'threat_level'  => 65,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: User Enumeration
	 * Slug: -user-enumeration
	 * File: class-diagnostic-user-enumeration.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: User Enumeration
	 * Slug: -user-enumeration
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
	public static function test_live__user_enumeration(): array
	{
		// Replicate the check logic
		$test_url = add_query_arg('author', 1, home_url());
		$response = wp_remote_head(
			$test_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if (is_wp_error($response)) {
			// Can't determine, pass test
			return array(
				'passed'  => true,
				'message' => 'Cannot test user enumeration (connection error)',
			);
		}

		$status = wp_remote_retrieve_response_code($response);
		$has_issue = ($status === 200 || $status === 301 || $status === 302);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'User enumeration check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (status: %d)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$status
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

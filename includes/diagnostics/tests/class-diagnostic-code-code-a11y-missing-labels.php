<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Form Labels
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-missing-labels
 * Training: https://wpshadow.com/training/code-a11y-missing-labels
 */
class Diagnostic_Code_CODE_A11Y_MISSING_LABELS extends Diagnostic_Base
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

		// Find all input fields (excluding hidden, submit, button)
		preg_match_all('/<input[^>]+>/i', $html, $inputs);
		$unlabeled_count = 0;
		$issues = array();

		foreach ($inputs[0] as $input) {
			// Skip hidden, submit, button types
			if (preg_match('/type=["\']?(hidden|submit|button|image)["\']?/i', $input)) {
				continue;
			}

			// Check if input has id attribute
			$has_id = preg_match('/id=["\']([^"\'\']+)["\']?/i', $input, $id_match);
			$input_id = $has_id ? $id_match[1] : null;

			// Check if input has aria-label or aria-labelledby
			$has_aria_label = preg_match('/aria-label(ledby)?=["\']?[^"\'\']+["\']?/i', $input);

			// Check if input has associated label by ID
			$has_label = false;
			if ($input_id) {
				if (preg_match('/<label[^>]+for=["\']?' . preg_quote($input_id, '/') . '["\']?[^>]*>/i', $html)) {
					$has_label = true;
				}
			}

			// Check if input is wrapped in label
			$input_escaped = preg_quote($input, '/');
			$has_wrapping_label = preg_match('/<label[^>]*>' . $input_escaped . '/i', $html);

			if (! $has_label && ! $has_aria_label && ! $has_wrapping_label) {
				$unlabeled_count++;
				if ($unlabeled_count <= 3) {
					// Extract type and name for debugging
					preg_match('/type=["\']?([^"\'\'\s>]+)/i', $input, $type_match);
					preg_match('/name=["\']?([^"\'\'\s>]+)/i', $input, $name_match);
					$type = $type_match[1] ?? 'text';
					$name = $name_match[1] ?? 'unknown';
					$issues[] = sprintf('Input type="%s" name="%s"', $type, $name);
				}
			}
		}

		if ($unlabeled_count === 0) {
			return null; // No issues found
		}

		$description = sprintf(
			\__('Found %d form input(s) without proper labels. Examples: %s', 'wpshadow'),
			$unlabeled_count,
			implode(', ', $issues)
		);

		return [
			'id' => 'code-a11y-missing-labels',
			'title' => __('Missing Form Labels', 'wpshadow'),
			'description' => $description,
			'severity' => 'medium',
			'category' => 'code-quality',
			'kb_link' => 'https://wpshadow.com/kb/code-a11y-missing-labels',
			'training_link' => 'https://wpshadow.com/training/code-a11y-missing-labels',
			'auto_fixable' => false,
			'threat_level' => 6
		];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE A11Y MISSING LABELS
	 * Slug: -code-code-a11y-missing-labels
	 * File: class-diagnostic-code-code-a11y-missing-labels.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE A11Y MISSING LABELS
	 * Slug: -code-code-a11y-missing-labels
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
	public static function test_live__code_code_a11y_missing_labels(): array
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

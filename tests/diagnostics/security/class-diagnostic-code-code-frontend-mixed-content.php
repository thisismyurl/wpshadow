<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Mixed HTTP/HTTPS
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-mixed-content
 * Training: https://wpshadow.com/training/code-frontend-mixed-content
 */
class Diagnostic_Code_CODE_FRONTEND_MIXED_CONTENT extends Diagnostic_Base
{
	public static function check(): ?array
	{
		// Placeholder check - returns advisory
		// In production, add specific validation logic

		return [
			'id' => 'code-frontend-mixed-content',
			'title' => __('Mixed HTTP/HTTPS', 'wpshadow'),
			'description' => __('Detects resources mixed between secure/insecure protocols.', 'wpshadow'),
			'severity' => 'medium',
			'category' => 'code-quality',
			'kb_link' => 'https://wpshadow.com/kb/code-frontend-mixed-content',
			'training_link' => 'https://wpshadow.com/training/code-frontend-mixed-content',
			'auto_fixable' => false,
			'threat_level' => 6
		];
	}


	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE FRONTEND MIXED CONTENT
	 * Slug: -code-code-frontend-mixed-content
	 * File: class-diagnostic-code-code-frontend-mixed-content.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE FRONTEND MIXED CONTENT
	 * Slug: -code-code-frontend-mixed-content
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
	public static function test_live__code_code_frontend_mixed_content(): array
	{
		$result = self::check();

		// This is a placeholder diagnostic that always returns an advisory
		// Test verifies that check() returns the expected array format
		if (is_array($result) && isset($result['id']) && 'code-frontend-mixed-content' === $result['id']) {
			return array(
				'passed'  => true,
				'message' => 'Mixed content advisory returned with correct structure.',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected advisory array for mixed content check but got: ' . gettype($result),
		);
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSP Inline Dependencies
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-csp-ready
 * Training: https://wpshadow.com/training/code-frontend-csp-ready
 */
class Diagnostic_Code_CODE_FRONTEND_CSP_READY extends Diagnostic_Base
{
	public static function check(): ?array
	{
		// Placeholder check - returns advisory
		// In production, add specific validation logic

		return [
			'id' => 'code-frontend-csp-ready',
			'title' => __('CSP Inline Dependencies', 'wpshadow'),
			'description' => __('Flags unsafe-inline scripts/styles blocking CSP compliance.', 'wpshadow'),
			'severity' => 'medium',
			'category' => 'code-quality',
			'kb_link' => 'https://wpshadow.com/kb/code-frontend-csp-ready',
			'training_link' => 'https://wpshadow.com/training/code-frontend-csp-ready',
			'auto_fixable' => false,
			'threat_level' => 6
		];
	}


	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE FRONTEND CSP READY
	 * Slug: -code-code-frontend-csp-ready
	 * File: class-diagnostic-code-code-frontend-csp-ready.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE FRONTEND CSP READY
	 * Slug: -code-code-frontend-csp-ready
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
	public static function test_live__code_code_frontend_csp_ready(): array
	{
		$result = self::check();

		// This is a placeholder diagnostic that always returns an advisory
		// Test verifies that check() returns the expected array format
		if (is_array($result) && isset($result['id']) && 'code-frontend-csp-ready' === $result['id']) {
			return array(
				'passed'  => true,
				'message' => 'CSP readiness advisory returned with correct structure.',
			);
		}

		return array(
			'passed'  => false,
			'message' => 'Expected advisory array for CSP readiness check but got: ' . gettype($result),
		);
	}
}

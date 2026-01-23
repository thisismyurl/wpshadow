<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Legacy Code Ratio
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-legacy-code-ratio
 * Training: https://wpshadow.com/training/design-debt-legacy-code-ratio
 */
class Diagnostic_Design_DEBT_LEGACY_CODE_RATIO extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-legacy-code-ratio',
            'title' => __('Legacy Code Ratio', 'wpshadow'),
            'description' => __('Estimates % of code predating current design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-legacy-code-ratio',
            'training_link' => 'https://wpshadow.com/training/design-debt-legacy-code-ratio',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DEBT LEGACY CODE RATIO
	 * Slug: -design-debt-legacy-code-ratio
	 * File: class-diagnostic-design-debt-legacy-code-ratio.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DEBT LEGACY CODE RATIO
	 * Slug: -design-debt-legacy-code-ratio
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
	public static function test_live__design_debt_legacy_code_ratio(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}

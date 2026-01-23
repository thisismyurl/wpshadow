<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

// TODO (Issue #XXX): Implement this diagnostic - requires deep code analysis/database inspection

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Direct Output in REST
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-rest-direct-output
 * Training: https://wpshadow.com/training/code-rest-direct-output
 */
class Diagnostic_Code_CODE_REST_DIRECT_OUTPUT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-rest-direct-output',
            'title' => __('Direct Output in REST', 'wpshadow'),
            'description' => __('Flags REST callbacks with echo/print instead of return.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-rest-direct-output',
            'training_link' => 'https://wpshadow.com/training/code-rest-direct-output',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE REST DIRECT OUTPUT
	 * Slug: -code-code-rest-direct-output
	 * File: class-diagnostic-code-code-rest-direct-output.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE REST DIRECT OUTPUT
	 * Slug: -code-code-rest-direct-output
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
	public static function test_live__code_code_rest_direct_output(): array {
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

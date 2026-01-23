<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hint Text Distinction
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-hint-text-distinction
 * Training: https://wpshadow.com/training/design-hint-text-distinction
 */
class Diagnostic_Design_HINT_TEXT_DISTINCTION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-hint-text-distinction',
            'title' => __('Hint Text Distinction', 'wpshadow'),
            'description' => __('Checks helper text distinct from labels/errors.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hint-text-distinction',
            'training_link' => 'https://wpshadow.com/training/design-hint-text-distinction',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design HINT TEXT DISTINCTION
	 * Slug: -design-hint-text-distinction
	 * File: class-diagnostic-design-hint-text-distinction.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design HINT TEXT DISTINCTION
	 * Slug: -design-hint-text-distinction
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
	public static function test_live__design_hint_text_distinction(): array {
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

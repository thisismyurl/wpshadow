<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: All-Caps Typography Used Sparingly
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-all-caps-sparingly
 * Training: https://wpshadow.com/training/design-all-caps-sparingly
 */
class Diagnostic_Design_ALL_CAPS_SPARINGLY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-all-caps-sparingly',
            'title' => __('All-Caps Typography Used Sparingly', 'wpshadow'),
            'description' => __('Verifies all-caps used only for labels/UI.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-all-caps-sparingly',
            'training_link' => 'https://wpshadow.com/training/design-all-caps-sparingly',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design ALL CAPS SPARINGLY
	 * Slug: -design-all-caps-sparingly
	 * File: class-diagnostic-design-all-caps-sparingly.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design ALL CAPS SPARINGLY
	 * Slug: -design-all-caps-sparingly
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
	public static function test_live__design_all_caps_sparingly(): array {
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

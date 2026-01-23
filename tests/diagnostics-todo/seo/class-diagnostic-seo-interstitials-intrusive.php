<?php
declare(strict_types=1);
/**
 * Intrusive Interstitials Diagnostic
 *
 * Philosophy: Avoid intrusive popups blocking content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Interstitials_Intrusive extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-interstitials-intrusive',
            'title' => 'Avoid Intrusive Interstitials',
            'description' => 'Ensure popups/interstitials are not intrusive and do not block content, particularly on mobile.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/intrusive-interstitials/',
            'training_link' => 'https://wpshadow.com/training/mobile-seo/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Interstitials Intrusive
	 * Slug: -seo-interstitials-intrusive
	 * File: class-diagnostic-seo-interstitials-intrusive.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Interstitials Intrusive
	 * Slug: -seo-interstitials-intrusive
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
	public static function test_live__seo_interstitials_intrusive(): array {
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

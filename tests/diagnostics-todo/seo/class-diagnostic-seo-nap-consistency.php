<?php
declare(strict_types=1);
/**
 * NAP Consistency Diagnostic
 *
 * Philosophy: Consistent business info across site
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_NAP_Consistency extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-nap-consistency',
            'title' => 'NAP Consistency',
            'description' => 'Ensure Name, Address, and Phone (NAP) are consistent across footer, contact page, and schema markup.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/nap-consistency/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO NAP Consistency
	 * Slug: -seo-nap-consistency
	 * File: class-diagnostic-seo-nap-consistency.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO NAP Consistency
	 * Slug: -seo-nap-consistency
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
	public static function test_live__seo_nap_consistency(): array {
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

<?php
declare(strict_types=1);
/**
 * Nofollow Internal Misuse Diagnostic
 *
 * Philosophy: Avoid nofollow on own pages
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Nofollow_Internal_Misuse extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-nofollow-internal-misuse',
            'title' => 'Nofollow Internal Links Misuse',
            'description' => 'Avoid using nofollow on internal links; reserve it for untrusted external links.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/nofollow-internal-links/',
            'training_link' => 'https://wpshadow.com/training/internal-linking/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Nofollow Internal Misuse
	 * Slug: -seo-nofollow-internal-misuse
	 * File: class-diagnostic-seo-nofollow-internal-misuse.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Nofollow Internal Misuse
	 * Slug: -seo-nofollow-internal-misuse
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
	public static function test_live__seo_nofollow_internal_misuse(): array {
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

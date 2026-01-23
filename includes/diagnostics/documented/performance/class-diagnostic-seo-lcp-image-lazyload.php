<?php
declare(strict_types=1);
/**
 * LCP Image Lazyload Diagnostic
 *
 * Philosophy: Avoid lazy-loading the largest contentful paint image
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_LCP_Image_Lazyload extends Diagnostic_Base {
    /**
     * Advisory: ensure critical above-the-fold images are not lazy-loaded.
     *
     * @return array|null
     */
    public static function check(): ?array {
        return [
            'id' => 'seo-lcp-image-lazyload',
            'title' => 'Avoid Lazy-Load on LCP Image',
            'description' => 'Ensure the largest above-the-fold image is not lazy-loaded and has explicit width/height to stabilize layout.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/lcp-image-best-practices/',
            'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO LCP Image Lazyload
	 * Slug: -seo-lcp-image-lazyload
	 * File: class-diagnostic-seo-lcp-image-lazyload.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO LCP Image Lazyload
	 * Slug: -seo-lcp-image-lazyload
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
	public static function test_live__seo_lcp_image_lazyload(): array {
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

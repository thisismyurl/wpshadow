<?php
declare(strict_types=1);
/**
 * Discourage Search Engines (blog_public) Diagnostic
 *
 * Philosophy: Technical SEO visibility control
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Discourage_Search_Engines extends Diagnostic_Base {
    /**
     * Check if the site is set to discourage search engines.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $blog_public = get_option('blog_public');
        if ($blog_public === '0' || $blog_public === 0) {
            return [
                'id' => 'seo-discourage-search-engines',
                'title' => 'Search Engine Visibility Disabled',
                'description' => 'WordPress is set to discourage search engines (noindex). Disable this in Settings → Reading for production sites.',
                'severity' => 'high',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/search-engine-visibility/',
                'training_link' => 'https://wpshadow.com/training/indexation-basics/',
                'auto_fixable' => false,
                'threat_level' => 80,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Discourage Search Engines
	 * Slug: -seo-discourage-search-engines
	 * File: class-diagnostic-seo-discourage-search-engines.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Discourage Search Engines
	 * Slug: -seo-discourage-search-engines
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
	public static function test_live__seo_discourage_search_engines(): array {
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

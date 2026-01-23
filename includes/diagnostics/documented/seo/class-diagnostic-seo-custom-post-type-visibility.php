<?php
declare(strict_types=1);
/**
 * Custom Post Type Visibility Diagnostic
 *
 * Philosophy: CPTs should be in sitemaps
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Custom_Post_Type_Visibility extends Diagnostic_Base {
    public static function check(): ?array {
        $post_types = get_post_types(['public' => true, '_builtin' => false], 'names');
        if (count($post_types) > 0) {
            return [
                'id' => 'seo-custom-post-type-visibility',
                'title' => 'Custom Post Type Sitemap Visibility',
                'description' => 'Verify custom post types are included in XML sitemaps and properly indexed.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/custom-post-types-seo/',
                'training_link' => 'https://wpshadow.com/training/cpt-optimization/',
                'auto_fixable' => false,
                'threat_level' => 25,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Custom Post Type Visibility
	 * Slug: -seo-custom-post-type-visibility
	 * File: class-diagnostic-seo-custom-post-type-visibility.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Custom Post Type Visibility
	 * Slug: -seo-custom-post-type-visibility
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
	public static function test_live__seo_custom_post_type_visibility(): array {
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

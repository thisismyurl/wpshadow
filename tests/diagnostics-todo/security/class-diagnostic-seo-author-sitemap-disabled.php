<?php
declare(strict_types=1);
/**
 * Author Sitemap Disabled Diagnostic
 *
 * Philosophy: Avoid low-value author archives on single-author sites
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Author_Sitemap_Disabled extends Diagnostic_Base {
    public static function check(): ?array {
        // Count active authors
        $author_count = count(get_users(array('who' => 'authors')));
        
        // Only flag if single author site
        if ($author_count > 1) {
            return null;
        }
        
        // Check if Yoast SEO is active and has author sitemap disabled
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $options = get_option('wpseo_xml');
            if (isset($options['disable_author_sitemap']) && $options['disable_author_sitemap']) {
                return null; // Already disabled
            }
        }
        
        return [
            'id' => 'seo-author-sitemap-disabled',
            'title' => 'Consider Disabling Author Sitemap',
            'description' => 'Single-author site detected. Consider disabling author archive sitemaps.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/author-archives-seo/',
            'training_link' => 'https://wpshadow.com/training/archive-templates-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Author Sitemap Disabled
	 * Slug: -seo-author-sitemap-disabled
	 * File: class-diagnostic-seo-author-sitemap-disabled.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Author Sitemap Disabled
	 * Slug: -seo-author-sitemap-disabled
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
	public static function test_live__seo_author_sitemap_disabled(): array {
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

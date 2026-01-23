<?php
declare(strict_types=1);
/**
 * Duplicate Analytics Tags Diagnostic
 *
 * Philosophy: Prevent double-counting and bloat from multiple tags
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Duplicate_Analytics_Tags extends Diagnostic_Base {
    /**
     * Heuristic: multiple analytics plugins active can duplicate tags.
     *
     * @return array|null
     */
    public static function check(): ?array {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = [
            'google-site-kit/google-site-kit.php',
            'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php',
            'monsterinsights/google-analytics-plugin.php',
        ];
        $active = 0;
        foreach ($plugins as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
                $active++;
            }
        }
        if ($active >= 2) {
            return [
                'id' => 'seo-duplicate-analytics-tags',
                'title' => 'Potential Duplicate Analytics Tags',
                'description' => 'Multiple analytics/GTM plugins are active. Verify tags are not duplicated to avoid double-counting and performance issues.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/duplicate-analytics-tags/',
                'training_link' => 'https://wpshadow.com/training/analytics-setup/',
                'auto_fixable' => false,
                'threat_level' => 40,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Duplicate Analytics Tags
	 * Slug: -seo-duplicate-analytics-tags
	 * File: class-diagnostic-seo-duplicate-analytics-tags.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Duplicate Analytics Tags
	 * Slug: -seo-duplicate-analytics-tags
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
	public static function test_live__seo_duplicate_analytics_tags(): array {
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

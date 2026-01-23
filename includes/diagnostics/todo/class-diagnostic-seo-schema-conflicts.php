<?php
declare(strict_types=1);
/**
 * Schema Conflicts Diagnostic
 *
 * Philosophy: Avoid duplicate/contradictory schema outputs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Schema_Conflicts extends Diagnostic_Base {
    /**
     * Heuristic: multiple schema/SEO plugins active can output conflicting markup.
     *
     * @return array|null
     */
    public static function check(): ?array {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $conflictPlugins = [
            'wordpress-seo/wp-seo.php',
            'wp-seopress/seopress.php',
            'schema-and-structured-data-for-wp/schema-and-structured-data-for-wp.php',
        ];
        $active = 0;
        foreach ($conflictPlugins as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
                $active++;
            }
        }
        if ($active >= 2) {
            return [
                'id' => 'seo-schema-conflicts',
                'title' => 'Potential Schema Output Conflicts',
                'description' => 'Multiple schema-capable plugins are active. This can duplicate or contradict structured data. Use a single source of truth.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/schema-conflicts/',
                'training_link' => 'https://wpshadow.com/training/structured-data/',
                'auto_fixable' => false,
                'threat_level' => 50,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Schema Conflicts
	 * Slug: -seo-schema-conflicts
	 * File: class-diagnostic-seo-schema-conflicts.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Schema Conflicts
	 * Slug: -seo-schema-conflicts
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
	public static function test_live__seo_schema_conflicts(): array {
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

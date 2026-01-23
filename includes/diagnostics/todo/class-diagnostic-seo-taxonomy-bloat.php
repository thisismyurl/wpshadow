<?php
declare(strict_types=1);
/**
 * Taxonomy Bloat Diagnostic
 *
 * Philosophy: Unused terms dilute crawl budget
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Taxonomy_Bloat extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $unused_terms = (int) $wpdb->get_var("SELECT COUNT(t.term_id) FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.count = 0");
        if ($unused_terms > 50) {
            return [
                'id' => 'seo-taxonomy-bloat',
                'title' => 'Unused Taxonomy Terms',
                'description' => sprintf('%d unused tags/categories detected. Clean up to improve crawl efficiency.', $unused_terms),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/taxonomy-cleanup/',
                'training_link' => 'https://wpshadow.com/training/taxonomy-optimization/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Taxonomy Bloat
	 * Slug: -seo-taxonomy-bloat
	 * File: class-diagnostic-seo-taxonomy-bloat.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Taxonomy Bloat
	 * Slug: -seo-taxonomy-bloat
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
	public static function test_live__seo_taxonomy_bloat(): array {
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

<?php
declare(strict_types=1);
/**
 * Excerpt Quality Diagnostic
 *
 * Philosophy: Hand-written excerpts improve CTR
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Excerpt_Quality extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $total = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'");
        $with_excerpt = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' AND post_excerpt != ''");
        $missing = $total - $with_excerpt;
        if ($missing > 10 && $missing > ($total * 0.3)) {
            return [
                'id' => 'seo-excerpt-quality',
                'title' => 'Hand-Written Excerpts Missing',
                'description' => sprintf('%d posts relying on auto-generated excerpts. Write custom excerpts for better meta descriptions.', $missing),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/excerpt-best-practices/',
                'training_link' => 'https://wpshadow.com/training/content-optimization/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Excerpt Quality
	 * Slug: -seo-excerpt-quality
	 * File: class-diagnostic-seo-excerpt-quality.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Excerpt Quality
	 * Slug: -seo-excerpt-quality
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
	public static function test_live__seo_excerpt_quality(): array {
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

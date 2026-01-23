<?php
declare(strict_types=1);
/**
 * Featured Image Missing Diagnostic
 *
 * Philosophy: Featured images improve social sharing
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Featured_Image_Missing extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $total = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'");
        $with_thumb = (int) $wpdb->get_var("SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_status = 'publish' AND p.post_type = 'post' AND pm.meta_key = '_thumbnail_id'");
        $missing = $total - $with_thumb;
        if ($missing > 5 && $missing > ($total * 0.2)) {
            return [
                'id' => 'seo-featured-image-missing',
                'title' => 'Posts Missing Featured Images',
                'description' => sprintf('%d posts without featured images. Add images for better social sharing and visual appeal.', $missing),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/featured-images/',
                'training_link' => 'https://wpshadow.com/training/image-seo/',
                'auto_fixable' => false,
                'threat_level' => 25,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Featured Image Missing
	 * Slug: -seo-featured-image-missing
	 * File: class-diagnostic-seo-featured-image-missing.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Featured Image Missing
	 * Slug: -seo-featured-image-missing
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
	public static function test_live__seo_featured_image_missing(): array {
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

<?php
declare(strict_types=1);
/**
 * Comment Spam Ratio Diagnostic
 *
 * Philosophy: High spam ratio wastes crawl budget
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Comment_Spam_Ratio extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $approved = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_approved = '1'");
        $spam = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
        if ($spam > 100 && $spam > ($approved * 2)) {
            return [
                'id' => 'seo-comment-spam-ratio',
                'title' => 'High Comment Spam Ratio',
                'description' => sprintf('%d spam comments vs %d approved. Clean up spam to reduce database bloat.', $spam, $approved),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/comment-spam/',
                'training_link' => 'https://wpshadow.com/training/comment-management/',
                'auto_fixable' => false,
                'threat_level' => 25,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Comment Spam Ratio
	 * Slug: -seo-comment-spam-ratio
	 * File: class-diagnostic-seo-comment-spam-ratio.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Comment Spam Ratio
	 * Slug: -seo-comment-spam-ratio
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
	public static function test_live__seo_comment_spam_ratio(): array {
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

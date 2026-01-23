<?php
declare(strict_types=1);
/**
 * Pingback Spam Diagnostic
 *
 * Philosophy: Spam pingbacks affect site quality
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Pingback_Spam extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $pingbacks = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_type = 'pingback' AND comment_approved = 'spam'");
        if ($pingbacks > 100) {
            return [
                'id' => 'seo-pingback-spam',
                'title' => 'Pingback Spam Detected',
                'description' => sprintf('%d spam pingbacks detected. Clean up and consider disabling pingbacks if not useful.', $pingbacks),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/pingback-spam/',
                'training_link' => 'https://wpshadow.com/training/comment-management/',
                'auto_fixable' => false,
                'threat_level' => 15,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Pingback Spam
	 * Slug: -seo-pingback-spam
	 * File: class-diagnostic-seo-pingback-spam.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Pingback Spam
	 * Slug: -seo-pingback-spam
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
	public static function test_live__seo_pingback_spam(): array {
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

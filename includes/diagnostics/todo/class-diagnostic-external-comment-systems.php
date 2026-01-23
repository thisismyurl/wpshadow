<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: External Comment Systems (THIRD-003)
 * 
 * Detects Disqus, Facebook Comments, etc.
 * Philosophy: Educate (#5) about comment system alternatives.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_External_Comment_Systems extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for external comment systems
        $has_disqus = function_exists('dsq_init');
        $has_facebook = get_option('fb_app_id');
        $has_custom = apply_filters('wpshadow_external_comments_detected', false);
        
        if ($has_disqus || $has_facebook || $has_custom) {
            return array(
                'id' => 'external-comment-systems',
                'title' => __('External Comment System Detected', 'wpshadow'),
                'description' => __('Using an external comment system adds extra requests and may impact performance. Monitor third-party service uptime.', 'wpshadow'),
                'severity' => 'info',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/external-comment-systems/',
                'training_link' => 'https://wpshadow.com/training/comment-performance/',
                'auto_fixable' => false,
                'threat_level' => 30,
            );
        }
        return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: External Comment Systems
	 * Slug: -external-comment-systems
	 * File: class-diagnostic-external-comment-systems.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: External Comment Systems
	 * Slug: -external-comment-systems
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
	public static function test_live__external_comment_systems(): array {
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

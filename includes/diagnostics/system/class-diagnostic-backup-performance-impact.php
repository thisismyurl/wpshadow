<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: wp_comments Foreign Key Missing (DB-020)
 * 
 * Checks if wp_comments lacks foreign key to wp_posts.
 * Philosophy: Show value (#9) with data integrity improvements.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Comments_Foreign_Key_Missing extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		global $wpdb;

		// Check for foreign key from wp_comments.comment_post_ID to wp_posts.ID
		$constraint_count = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'comment_post_ID' AND REFERENCED_TABLE_NAME = %s AND REFERENCED_COLUMN_NAME = %s",
			DB_NAME,
			$wpdb->comments,
			$wpdb->posts,
			'ID'
		));

		if ((int) $constraint_count === 0) {
			return array(
				'id' => 'comments-foreign-key-missing',
				'title' => __('Comments table lacks post foreign key', 'wpshadow'),
				'description' => __('wp_comments is missing a foreign key to wp_posts. Add a foreign key to improve referential integrity and query performance.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/comments-foreign-key/',
				'training_link' => 'https://wpshadow.com/training/database-integrity/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Comments Foreign Key Missing
	 * Slug: -backup-performance-impact
	 * File: class-diagnostic-backup-performance-impact.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Comments Foreign Key Missing
	 * Slug: -backup-performance-impact
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
	public static function test_live__backup_performance_impact(): array {
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

<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Users_Comment_Activity_Top_Authors extends Diagnostic_Base {
	protected static $slug = 'users-comment-activity-top-authors';

	protected static $title = 'Users Comment Activity Top Authors';

	protected static $description = 'Automatically initialized lean diagnostic for Users Comment Activity Top Authors. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'users-comment-activity-top-authors';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Top Commenters/Contributors', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Who is most active in comments?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'users';
	}

	/**
	 * Get threat level
	 *
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 15;
	}

	/**
	 * Run diagnostic test
	 *
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		// STUB: Implement users-comment-activity-top-authors test
		// Philosophy focus: Commandment #1, 8, 9
		//
		// Data collection strategy:
		// - Gather relevant metrics from WordPress
		// - Calculate or query necessary values
		// - Return structured result
		//
		// KB Article: https://wpshadow.com/kb/users-comment-activity-top-authors
		// Training: https://wpshadow.com/training/category-users
		//
		// User impact: Give site owners visibility into team productivity and customer engagement patterns. Identify inactive accounts, track admin activity.

		return array(
			'status'  => 'todo',
			'message' => 'Diagnostic not yet implemented',
			'data'    => array(),
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/users-comment-activity-top-authors';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-users';
	}

	public static function check(): ?array {
		// Check if top authors have comment activity
		$top_authors = new \WP_User_Query(
			array(
				'role__in' => array( 'author', 'editor', 'administrator' ),
				'number'   => 10,
				'orderby'  => 'post_count',
				'order'    => 'DESC',
				'fields'   => 'ID',
			)
		);

		$author_ids       = $top_authors->get_results();
		$inactive_authors = 0;

		foreach ( $author_ids as $author_id ) {
			$comment_count = get_comments(
				array(
					'count'    => true,
					'user_id'  => $author_id,
					'approved' => 1,
					'status'   => 'approve',
				)
			);

			if ( $comment_count === 0 ) {
				++$inactive_authors;
			}
		}

		// Flag if many top authors have no comment engagement
		if ( $inactive_authors > ( count( $author_ids ) * 0.5 ) ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'users-comment-activity-top-authors',
				'Users Comment Activity Top Authors',
				'Low comment engagement from top authors. Encourage team discussion and peer review.',
				'users',
				'low',
				20,
				'users-comment-activity-top-authors'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Users Comment Activity Top Authors
	 * Slug: users-comment-activity-top-authors
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Users Comment Activity Top Authors. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_users_comment_activity_top_authors(): array {
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
			'passed'  => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}
}

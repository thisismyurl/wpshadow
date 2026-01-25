<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Draft Posts
 * Checks for accumulated draft posts
 */
class Test_Database_Draft_Posts extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$draft_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'draft'"
		);

		if ( $draft_posts > 100 ) {
			return array(
				'id'           => 'database-draft-posts',
				'title'        => 'Many Unpublished Draft Posts',
				'threat_level' => 20,
				'description'  => sprintf(
					'Found %d draft posts. Consider publishing or deleting old ones.',
					$draft_posts
				),
			);
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_draft_posts(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Draft count is normal' : 'Many draft posts detected',
		);
	}
}

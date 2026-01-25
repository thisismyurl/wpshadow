<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Excessive Revisions
 * Checks if post revisions exceed reasonable threshold (>50 per post avg)
 */
class Test_Database_Excessive_Revisions extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$revision_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		$post_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'"
		);

		if ( $post_count && $revision_count / $post_count > 50 ) {
			return array(
				'id'           => 'database-excessive-revisions',
				'title'        => 'Excessive Post Revisions Detected',
				'threat_level' => 40,
				'description'  => sprintf(
					'Database has %d revisions (%d avg per post). Consider cleanup.',
					$revision_count,
					$post_count > 0 ? intval( $revision_count / $post_count ) : 0
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
	public static function test_live_excessive_revisions(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Revision count is healthy' : 'Excessive revisions detected',
		);
	}
}

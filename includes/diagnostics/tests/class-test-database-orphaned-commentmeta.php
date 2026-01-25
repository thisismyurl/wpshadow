<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Orphaned CommentMeta
 * Checks for comment metadata without corresponding comments
 */
class Test_Database_Orphaned_CommentMeta extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->commentmeta} cm
			 LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID
			 WHERE c.comment_ID IS NULL"
		);

		if ( $orphaned_meta > 100 ) {
			return array(
				'id'           => 'database-orphaned-commentmeta',
				'title'        => 'Orphaned Comment Metadata',
				'threat_level' => 25,
				'description'  => sprintf(
					'Found %d orphaned commentmeta entries without corresponding comments.',
					$orphaned_meta
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
	public static function test_live_orphaned_commentmeta(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'CommentMeta integrity is good' : 'Orphaned commentmeta detected',
		);
	}
}

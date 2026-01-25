<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Spam Comments
 * Checks if spam comments exceed 1% of total comments
 */
class Test_Database_Spam_Comments extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$total_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments}"
		);

		$spam_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
		);

		if ( $total_comments && $spam_comments / $total_comments > 0.01 ) {
			return array(
				'id'           => 'database-spam-comments',
				'title'        => 'High Spam Comment Ratio',
				'threat_level' => 30,
				'description'  => sprintf(
					'%d spam comments out of %d total (%.1f%%)',
					$spam_comments,
					$total_comments,
					$total_comments > 0 ? ( $spam_comments / $total_comments * 100 ) : 0
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
	public static function test_live_spam_comments(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Spam comment ratio is normal' : 'High spam ratio detected',
		);
	}
}

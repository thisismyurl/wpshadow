<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Auto-Draft Posts
 * Checks for accumulated auto-draft posts
 */
class Test_Database_Auto_Drafts extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$auto_drafts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"
		);

		if ( $auto_drafts > 50 ) {
			return array(
				'id'           => 'database-auto-drafts',
				'title'        => 'Excessive Auto-Draft Posts',
				'threat_level' => 25,
				'description'  => sprintf(
					'Found %d auto-draft posts. These can be safely deleted.',
					$auto_drafts
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
	public static function test_live_auto_drafts(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Auto-draft count is normal' : 'Excessive auto-drafts detected',
		);
	}
}

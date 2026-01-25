<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Orphaned UserMeta
 * Checks for user metadata without corresponding users
 */
class Test_Database_Orphaned_UserMeta extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} um
			 LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
			 WHERE u.ID IS NULL"
		);

		if ( $orphaned_meta > 50 ) {
			return array(
				'id'           => 'database-orphaned-usermeta',
				'title'        => 'Orphaned User Metadata',
				'threat_level' => 25,
				'description'  => sprintf(
					'Found %d orphaned usermeta entries without corresponding users.',
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
	public static function test_live_orphaned_usermeta(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'UserMeta integrity is good' : 'Orphaned usermeta detected',
		);
	}
}

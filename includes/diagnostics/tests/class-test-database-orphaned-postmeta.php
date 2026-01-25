<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Orphaned PostMeta
 * Checks for post metadata without corresponding posts
 */
class Test_Database_Orphaned_PostMeta extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			 WHERE p.ID IS NULL"
		);

		if ( $orphaned_meta > 100 ) {
			return array(
				'id'           => 'database-orphaned-postmeta',
				'title'        => 'Orphaned Post Metadata',
				'threat_level' => 25,
				'description'  => sprintf(
					'Found %d orphaned postmeta entries without corresponding posts.',
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
	public static function test_live_orphaned_postmeta(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'PostMeta integrity is good' : 'Orphaned postmeta detected',
		);
	}
}

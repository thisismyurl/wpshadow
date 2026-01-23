<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Unused PostMeta Keys
 * Checks for postmeta keys used by only a few posts (likely unused/abandoned)
 */
class Test_Database_Unused_PostMeta_Keys extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		global $wpdb;

		$unused_keys = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key) FROM {$wpdb->postmeta}
			 WHERE meta_key NOT IN ('_edit_lock', '_edit_last', '_thumbnail_id')
			 GROUP BY meta_key HAVING COUNT(*) < 5"
		);

		if ($unused_keys > 50) {
			return array(
				'id'            => 'database-unused-postmeta-keys',
				'title'         => 'Many Unused PostMeta Keys',
				'threat_level'  => 20,
				'description'   => sprintf(
					'Found %d postmeta keys used by fewer than 5 posts. Likely abandoned.',
					$unused_keys
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
	public static function test_live_unused_postmeta_keys(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'PostMeta usage is clean' : 'Unused postmeta keys detected',
		);
	}
}

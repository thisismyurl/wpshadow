<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Duplicate Options
 * Checks for duplicate options (same name in database)
 */
class Test_Database_Duplicate_Options extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		global $wpdb;

		$duplicate_options = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT option_name FROM {$wpdb->options}
				GROUP BY option_name HAVING COUNT(*) > 1
			) AS duplicates"
		);

		if ($duplicate_options > 0) {
			return array(
				'id'            => 'database-duplicate-options',
				'title'         => 'Duplicate Options Found',
				'threat_level'  => 50,
				'description'   => sprintf(
					'Found %d options with duplicate names. This indicates data corruption.',
					$duplicate_options
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
	public static function test_live_duplicate_options(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'No duplicate options detected' : 'Duplicate options found',
		);
	}
}

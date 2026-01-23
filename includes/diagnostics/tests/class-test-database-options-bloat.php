<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Options Bloat
 * Checks for excessive options in database
 */
class Test_Database_Options_Bloat extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		global $wpdb;

		$total_options = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}"
		);

		if ($total_options > 5000) {
			return array(
				'id'            => 'database-options-bloat',
				'title'         => 'Excessive Options in Database',
				'threat_level'  => 30,
				'description'   => sprintf(
					'Database contains %d options. Normal range is 500-2000.',
					$total_options
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
	public static function test_live_options_bloat(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Options count is normal' : 'Database options bloat detected',
		);
	}
}

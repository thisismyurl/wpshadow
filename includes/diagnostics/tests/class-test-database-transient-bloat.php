<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Transient Bloat
 * Checks for expired transients accumulating in database
 */
class Test_Database_Transient_Bloat extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		global $wpdb;

		$expired_transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%' AND option_value LIKE '%:0:%'"
		);

		if ($expired_transients > 100) {
			return array(
				'id'            => 'database-transient-bloat',
				'title'         => 'Expired Transients Accumulating',
				'threat_level'  => 30,
				'description'   => sprintf(
					'Found %d expired transients in database. Should be auto-cleaned.',
					$expired_transients
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
	public static function test_live_transient_bloat(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Transient count is normal' : 'Expired transients detected',
		);
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Slow Query Log
 * Checks if slow query log is enabled and has entries
 */
class Test_Database_Slow_Query_Log extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		global $wpdb;

		$slow_log_enabled = $wpdb->get_var(
			"SELECT @@slow_query_log"
		);

		$slow_query_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM mysql.slow_log"
		);

		if ($slow_log_enabled && $slow_query_count > 100) {
			return array(
				'id'            => 'database-slow-query-log',
				'title'         => 'High Number of Slow Queries',
				'threat_level'  => 40,
				'description'   => sprintf(
					'Slow query log has %d entries. Review and optimize queries.',
					$slow_query_count
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
	public static function test_live_slow_query_log(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Slow query count is normal' : 'Slow queries detected',
		);
	}
}

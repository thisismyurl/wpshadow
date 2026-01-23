<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Needs Optimization
 * Checks if database tables have fragmentation
 */
class Test_Database_Needs_Optimization extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		global $wpdb;

		$fragmented_tables = $wpdb->get_var(
			"SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
			 WHERE TABLE_SCHEMA = %s AND DATA_FREE > 0",
			DB_NAME
		);

		if ($fragmented_tables > 0) {
			return array(
				'id'            => 'database-needs-optimization',
				'title'         => 'Database Tables Need Optimization',
				'threat_level'  => 35,
				'description'   => sprintf(
					'%d tables have fragmentation. Run OPTIMIZE TABLE on them.',
					$fragmented_tables
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
	public static function test_live_needs_optimization(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Database is optimized' : 'Database fragmentation detected',
		);
	}
}

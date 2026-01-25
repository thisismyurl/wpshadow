<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Missing Indexes
 * Checks for tables without proper indexes
 */
class Test_Database_Missing_Indexes extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$tables_no_indexes = $wpdb->get_var(
			'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
			 LEFT JOIN INFORMATION_SCHEMA.STATISTICS ON TABLES.TABLE_NAME = STATISTICS.TABLE_NAME
			 WHERE TABLES.TABLE_SCHEMA = %s AND STATISTICS.INDEX_NAME IS NULL',
			DB_NAME
		);

		if ( $tables_no_indexes > 0 ) {
			return array(
				'id'           => 'database-missing-indexes',
				'title'        => 'Database Tables Missing Indexes',
				'threat_level' => 45,
				'description'  => sprintf(
					'%d tables lack proper indexes, affecting query performance.',
					$tables_no_indexes
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
	public static function test_live_missing_indexes(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'All tables have indexes' : 'Missing indexes detected',
		);
	}
}

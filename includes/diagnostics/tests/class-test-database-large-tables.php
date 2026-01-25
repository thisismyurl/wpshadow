<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Large Tables
 * Checks for exceptionally large tables that may need partitioning
 */
class Test_Database_Large_Tables extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$large_tables = $wpdb->get_results(
			'SELECT TABLE_NAME, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
			 FROM INFORMATION_SCHEMA.TABLES
			 WHERE TABLE_SCHEMA = %s AND (data_length + index_length) > 104857600',
			DB_NAME
		);

		if ( count( $large_tables ) > 0 ) {
			$table_list = implode(
				', ',
				array_map(
					function ( $t ) {
						return $t->TABLE_NAME . ' (' . $t->size_mb . 'MB)';
					},
					$large_tables
				)
			);
			return array(
				'id'           => 'database-large-tables',
				'title'        => 'Very Large Database Tables',
				'threat_level' => 30,
				'description'  => sprintf(
					'Tables over 100MB: %s. Consider archiving old data.',
					$table_list
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
	public static function test_live_large_tables(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Table sizes are normal' : 'Large tables detected',
		);
	}
}

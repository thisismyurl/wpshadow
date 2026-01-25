<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Charset Mismatch
 * Checks if tables use consistent character set encoding
 */
class Test_Database_Charset_Mismatch extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$charsets = $wpdb->get_col(
			'SELECT DISTINCT TABLE_COLLATION FROM INFORMATION_SCHEMA.TABLES
			 WHERE TABLE_SCHEMA = %s',
			DB_NAME
		);

		if ( count( $charsets ) > 1 ) {
			return array(
				'id'           => 'database-charset-mismatch',
				'title'        => 'Database Character Set Mismatch',
				'threat_level' => 40,
				'description'  => sprintf(
					'Database tables use %d different character sets: %s',
					count( $charsets ),
					implode( ', ', $charsets )
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
	public static function test_live_charset_mismatch(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Character sets are consistent' : 'Character set mismatch found',
		);
	}
}

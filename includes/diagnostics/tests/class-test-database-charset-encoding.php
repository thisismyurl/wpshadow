<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Charset Encoding
 *
 * Verifies database is using proper UTF-8 encoding for international content.
 * Incorrect encoding causes character display issues and data corruption.
 *
 * @since 1.2.0
 */
class Test_Database_Charset_Encoding extends Diagnostic_Base {


	/**
	 * Check database charset encoding
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$charset_check = self::verify_database_charset();

		if ( $charset_check['threat_level'] === 0 ) {
			return null;
		}

		return array(
			'threat_level'  => $charset_check['threat_level'],
			'threat_color'  => 'orange',
			'passed'        => false,
			'issue'         => $charset_check['issue'],
			'metadata'      => $charset_check,
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-database-charset/',
			'training_link' => 'https://wpshadow.com/training/wordpress-internationalization/',
		);
	}

	/**
	 * Guardian Sub-Test: Database connection charset
	 *
	 * @return array Test result
	 */
	public static function test_db_connection_charset(): array {
		global $wpdb;

		$charset   = $wpdb->get_results( "SHOW VARIABLES WHERE variable_name = 'character_set_client'" );
		$collation = $wpdb->get_results( "SHOW VARIABLES WHERE variable_name = 'collation_connection'" );

		$client_charset       = 'Unknown';
		$connection_collation = 'Unknown';

		if ( ! empty( $charset ) ) {
			$client_charset = $charset[0]->Value ?? 'Unknown';
		}

		if ( ! empty( $collation ) ) {
			$connection_collation = $collation[0]->Value ?? 'Unknown';
		}

		$ok = strpos( $client_charset, 'utf8' ) !== false;

		return array(
			'test_name'            => 'DB Connection Charset',
			'character_set_client' => $client_charset,
			'collation_connection' => $connection_collation,
			'passed'               => $ok,
			'description'          => $ok ? 'Using UTF-8 encoding' : sprintf( 'Using %s (not UTF-8)', $client_charset ),
		);
	}

	/**
	 * Guardian Sub-Test: Table charset compliance
	 *
	 * @return array Test result
	 */
	public static function test_table_charset(): array {
		global $wpdb;

		// Get all tables and their charsets
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT table_name, table_collation FROM information_schema.TABLES WHERE table_schema = %s',
				DB_NAME
			)
		);

		$non_utf8_tables = array();

		foreach ( $results as $table ) {
			if ( strpos( $table->table_collation, 'utf8' ) === false ) {
				$non_utf8_tables[] = array(
					'table'     => $table->table_name,
					'collation' => $table->table_collation,
				);
			}
		}

		return array(
			'test_name'       => 'Table Charset Compliance',
			'non_utf8_tables' => $non_utf8_tables,
			'passed'          => empty( $non_utf8_tables ),
			'description'     => empty( $non_utf8_tables ) ? 'All tables use UTF-8' : sprintf( '%d tables not UTF-8', count( $non_utf8_tables ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Column charset compliance
	 *
	 * @return array Test result
	 */
	public static function test_column_charset(): array {
		global $wpdb;

		// Check postmeta table for character set issues
		$results = $wpdb->get_results(
			'SELECT COLUMN_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLLATION_NAME IS NOT NULL LIMIT 10'
		);

		$non_utf8_columns = array();

		foreach ( $results as $column ) {
			if ( strpos( $column->COLLATION_NAME, 'utf8' ) === false ) {
				$non_utf8_columns[] = array(
					'column'    => $column->COLUMN_NAME,
					'collation' => $column->COLLATION_NAME,
				);
			}
		}

		return array(
			'test_name'        => 'Column Charset Compliance',
			'non_utf8_columns' => $non_utf8_columns,
			'passed'           => empty( $non_utf8_columns ),
			'description'      => empty( $non_utf8_columns ) ? 'All columns use UTF-8' : sprintf( '%d columns not UTF-8', count( $non_utf8_columns ) ),
		);
	}

	/**
	 * Guardian Sub-Test: WordPress charset constant
	 *
	 * @return array Test result
	 */
	public static function test_wp_charset_constant(): array {
		$wp_charset = get_option( 'blog_charset' );
		$ok         = strpos( $wp_charset, 'utf-8' ) !== false || strpos( $wp_charset, 'UTF-8' ) !== false;

		return array(
			'test_name'   => 'WordPress Charset Setting',
			'charset'     => $wp_charset,
			'passed'      => $ok,
			'description' => $ok ? sprintf( 'Set to %s (UTF-8)', $wp_charset ) : sprintf( 'Set to %s (not UTF-8)', $wp_charset ),
		);
	}

	/**
	 * Verify database charset
	 *
	 * @return array Charset verification results
	 */
	private static function verify_database_charset(): array {
		global $wpdb;

		$threat_level = 0;
		$issues       = array();

		// Check connection charset
		$charset = $wpdb->get_results( "SHOW VARIABLES WHERE variable_name = 'character_set_client'" );
		if ( ! empty( $charset ) && strpos( $charset[0]->Value, 'utf8' ) === false ) {
			$issues[]     = 'Database connection not using UTF-8';
			$threat_level = max( $threat_level, 50 );
		}

		// Check table charsets
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(*) as cnt FROM information_schema.TABLES WHERE table_schema = %s AND table_collation NOT LIKE 'utf8%'",
				DB_NAME
			)
		);

		if ( $results && $results[0]->cnt > 0 ) {
			$issues[]     = sprintf( '%d tables not using UTF-8 charset', $results[0]->cnt );
			$threat_level = max( $threat_level, 40 );
		}

		// Check WordPress charset setting
		$wp_charset = get_option( 'blog_charset' );
		if ( strpos( $wp_charset, 'utf-8' ) === false && strpos( $wp_charset, 'UTF-8' ) === false ) {
			$issues[]     = sprintf( 'WordPress set to %s (not UTF-8)', $wp_charset );
			$threat_level = max( $threat_level, 30 );
		}

		$issue = ! empty( $issues ) ? implode( '; ', $issues ) : 'Database encoding is properly configured for UTF-8';

		return array(
			'threat_level' => $threat_level,
			'issue'        => $issue,
			'wp_charset'   => $wp_charset,
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Database Charset Encoding';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Verifies database is using proper UTF-8 encoding for international content';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Configuration';
	}
}

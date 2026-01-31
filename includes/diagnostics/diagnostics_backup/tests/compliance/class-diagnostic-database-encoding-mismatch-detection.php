<?php
/**
 * Database Encoding Mismatch Detection Diagnostic
 *
 * Detects encoding mismatches between database, tables, and connections.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Encoding Mismatch Detection Class
 *
 * Tests encoding consistency.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Database_Encoding_Mismatch_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-encoding-mismatch-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Encoding Mismatch Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects encoding mismatches between database, tables, and connections';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$encoding_check = self::check_encoding_consistency();
		
		if ( $encoding_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $encoding_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-encoding-mismatch-detection',
				'meta'         => array(
					'db_charset'                => $encoding_check['db_charset'],
					'connection_charset'        => $encoding_check['connection_charset'],
					'wp_config_charset'         => $encoding_check['wp_config_charset'],
					'tables_with_mismatches'    => $encoding_check['tables_with_mismatches'],
				),
			);
		}

		return null;
	}

	/**
	 * Check encoding consistency.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_encoding_consistency() {
		global $wpdb;

		$check = array(
			'has_issues'             => false,
			'issues'                 => array(),
			'db_charset'             => '',
			'connection_charset'     => '',
			'wp_config_charset'      => defined( 'DB_CHARSET' ) ? DB_CHARSET : '',
			'tables_with_mismatches' => array(),
		);

		// Get database default charset.
		$db_charset_result = $wpdb->get_row( "SHOW VARIABLES LIKE 'character_set_database'" );
		
		if ( $db_charset_result && isset( $db_charset_result->Value ) ) {
			$check['db_charset'] = $db_charset_result->Value;
		}

		// Get connection charset.
		$connection_charset_result = $wpdb->get_row( "SHOW VARIABLES LIKE 'character_set_connection'" );
		
		if ( $connection_charset_result && isset( $connection_charset_result->Value ) ) {
			$check['connection_charset'] = $connection_charset_result->Value;
		}

		// Check for charset mismatches in core tables.
		$tables = array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->options,
			$wpdb->terms,
			$wpdb->term_taxonomy,
			$wpdb->term_relationships,
		);

		foreach ( $tables as $table ) {
			$table_status = $wpdb->get_row(
				$wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ),
				ARRAY_A
			);

			if ( $table_status && isset( $table_status['Collation'] ) ) {
				$table_charset = substr( $table_status['Collation'], 0, strpos( $table_status['Collation'], '_' ) );

				// Check if table charset differs from database charset.
				if ( ! empty( $check['db_charset'] ) && $table_charset !== $check['db_charset'] ) {
					$check['tables_with_mismatches'][] = array(
						'table'   => basename( $table ),
						'charset' => $table_charset,
					);
				}
			}
		}

		// Detect issues.
		if ( $check['connection_charset'] !== $check['db_charset'] && ! empty( $check['connection_charset'] ) && ! empty( $check['db_charset'] ) ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: 1: connection charset, 2: database charset */
				__( 'Connection charset (%1$s) does not match database charset (%2$s)', 'wpshadow' ),
				$check['connection_charset'],
				$check['db_charset']
			);
		}

		if ( $check['wp_config_charset'] !== $check['db_charset'] && ! empty( $check['wp_config_charset'] ) && ! empty( $check['db_charset'] ) ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: 1: DB_CHARSET, 2: database charset */
				__( 'DB_CHARSET (%1$s) does not match actual database charset (%2$s)', 'wpshadow' ),
				$check['wp_config_charset'],
				$check['db_charset']
			);
		}

		if ( ! empty( $check['tables_with_mismatches'] ) ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of tables */
				__( '%d tables have charset different from database default', 'wpshadow' ),
				count( $check['tables_with_mismatches'] )
			);
		}

		// Check if still using old utf8 (should be utf8mb4).
		if ( 'utf8' === $check['db_charset'] ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'Database using old utf8 encoding (should be utf8mb4 for emoji support)', 'wpshadow' );
		}

		return $check;
	}
}

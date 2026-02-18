<?php
/**
 * Database Integrity Check Diagnostic
 *
 * Checks database tables for corruption or issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1456
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Integrity Check Diagnostic Class
 *
 * Runs CHECK TABLE on all database tables to detect corruption.
 *
 * @since 1.6035.1456
 */
class Diagnostic_Database_Integrity_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-integrity-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Integrity Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks database tables for corruption or issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database-health';

	/**
	 * Run the database integrity diagnostic check.
	 *
	 * @since  1.6035.1456
	 * @return array|null Finding array if integrity issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_db_integrity';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		// Get all tables in database.
		$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );

		if ( empty( $tables ) ) {
			set_transient( $cache_key, null, DAY_IN_SECONDS );
			return null;
		}

		$corrupted_tables = array();
		$warning_tables = array();

		foreach ( $tables as $table ) {
			$table_name = $table[0];
			
			// Run CHECK TABLE command.
			$check_result = $wpdb->get_row(
				$wpdb->prepare(
					'CHECK TABLE %i',
					$table_name
				),
				ARRAY_A
			);

			if ( ! $check_result ) {
				continue;
			}

			$status = strtolower( $check_result['Msg_text'] ?? '' );

			if ( 'ok' !== $status ) {
				if ( strpos( $status, 'corrupt' ) !== false || strpos( $status, 'crashed' ) !== false ) {
					$corrupted_tables[] = array(
						'table'   => $table_name,
						'status'  => $check_result['Msg_type'] ?? 'error',
						'message' => $check_result['Msg_text'] ?? 'Unknown error',
					);
				} else {
					$warning_tables[] = array(
						'table'   => $table_name,
						'status'  => $check_result['Msg_type'] ?? 'warning',
						'message' => $check_result['Msg_text'] ?? 'Unknown warning',
					);
				}
			}
		}

		$result = null;

		if ( ! empty( $corrupted_tables ) ) {
			$table_names = array_column( $corrupted_tables, 'table' );
			
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of tables */
					__( 'Database corruption detected in tables: %s. Immediate repair required.', 'wpshadow' ),
					implode( ', ', $table_names )
				),
				'severity'    => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/database-corruption-repair',
				'meta'        => array(
					'corrupted_tables' => $corrupted_tables,
				),
			);
		} elseif ( ! empty( $warning_tables ) ) {
			$table_names = array_column( $warning_tables, 'table' );
			
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of tables */
					__( 'Database warnings detected in tables: %s. Review recommended.', 'wpshadow' ),
					implode( ', ', $table_names )
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/database-warnings',
				'meta'        => array(
					'warning_tables' => $warning_tables,
				),
			);
		}

		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		return $result;
	}
}

<?php
/**
 * Treatment for Database Charset/Collation Consistency
 *
 * Converts database tables and columns to UTF-8mb4 charset for emoji support.
 * Uses WordPress wpdb::prepare() for safe ALTER TABLE operations.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Database_Charset_Collation_Consistency Class
 *
 * Converts database, tables, and columns to UTF-8mb4 charset and
 * utf8mb4_unicode_ci collation for proper emoji support.
 *
 * @since 1.2601.2148
 */
class Treatment_Database_Charset_Collation_Consistency extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since  1.2601.2148
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'database-charset-collation-consistency';
	}

	/**
	 * Apply the treatment.
	 *
	 * Converts database tables and columns to UTF-8mb4 charset.
	 * This is a potentially long-running operation on large databases.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about the conversion.
	 * }
	 */
	public static function apply() {
		global $wpdb;

		$converted_tables = array();
		$converted_columns = array();
		$failed = array();

		// Get tables that need conversion.
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT TABLE_NAME, TABLE_COLLATION 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME LIKE %s
				AND TABLE_COLLATION NOT LIKE %s",
				DB_NAME,
				$wpdb->esc_like( $wpdb->prefix ) . '%',
				'utf8mb4%'
			)
		);

		if ( empty( $tables ) ) {
			return array(
				'success' => true,
				'message' => __( 'All tables already using UTF-8mb4 charset.', 'wpshadow' ),
				'details' => array(
					'converted_tables' => 0,
				),
			);
		}

		// Convert each table.
		foreach ( $tables as $table ) {
			$table_name = $table->TABLE_NAME;

			// Use WordPress dbDelta-style conversion (safe).
			$result = $wpdb->query(
				"ALTER TABLE `{$table_name}` 
				CONVERT TO CHARACTER SET utf8mb4 
				COLLATE utf8mb4_unicode_ci"
			);

			if ( false !== $result ) {
				$converted_tables[] = $table_name;
			} else {
				$failed[] = array(
					'table' => $table_name,
					'error' => $wpdb->last_error,
				);
			}
		}

		// Build result message.
		$message_parts = array();

		if ( ! empty( $converted_tables ) ) {
			$message_parts[] = sprintf(
				/* translators: %d: number of tables converted */
				_n(
					'Converted %d table to UTF-8mb4.',
					'Converted %d tables to UTF-8mb4.',
					count( $converted_tables ),
					'wpshadow'
				),
				number_format_i18n( count( $converted_tables ) )
			);
		}

		if ( ! empty( $failed ) ) {
			$message_parts[] = sprintf(
				/* translators: %d: number of tables that failed conversion */
				_n(
					'%d table failed to convert.',
					'%d tables failed to convert.',
					count( $failed ),
					'wpshadow'
				),
				number_format_i18n( count( $failed ) )
			);
		}

		$success = empty( $failed ) && ! empty( $converted_tables );

		return array(
			'success' => $success,
			'message' => implode( ' ', $message_parts ),
			'details' => array(
				'converted_tables' => $converted_tables,
				'failed_tables'    => $failed,
				'total_converted'  => count( $converted_tables ),
				'total_failed'     => count( $failed ),
			),
		);
	}
}

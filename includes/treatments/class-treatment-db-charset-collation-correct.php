<?php
/**
 * Treatment: DB Charset and Collation Correct
 *
 * Converts WordPress core tables to the utf8mb4 character set with the
 * utf8mb4_unicode_ci collation. The legacy "utf8" MySQL encoding only stores
 * the Basic Multilingual Plane (3 bytes per character) and cannot store emoji
 * or many CJK ideographs. utf8mb4 uses up to 4 bytes and covers the full
 * Unicode range.
 *
 * What `apply()` does:
 *  1. Records the current charset and collation of each table.
 *  2. Runs `ALTER TABLE ... CONVERT TO CHARACTER SET utf8mb4
 *     COLLATE utf8mb4_unicode_ci` on each WordPress core table.
 *  3. Stores the original charset/collation map for undo().
 *
 * What `undo()` does:
 *  Converts tables back to their originally recorded charset/collation. Note
 *  that converting from utf8mb4 back to utf8 can silently discard any 4-byte
 *  characters (emoji, etc.) that were stored during the utf8mb4 period. An
 *  explicit warning is returned to inform the admin.
 *
 * Risk level: high (table-level schema change, potentially slow on large tables)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

require_once __DIR__ . '/trait-database-schema-helpers.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter

/**
 * Converts WordPress core tables to utf8mb4_unicode_ci.
 */
class Treatment_Db_Charset_Collation_Correct extends Treatment_Base {

	use Database_Schema_Helpers;

	/**
	 * Treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'db-charset-collation-correct';

	const OPTION_KEY       = 'wpshadow_db_charset_backup';
	const TARGET_CHARSET   = 'utf8mb4';
	const TARGET_COLLATION = 'utf8mb4_unicode_ci';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	/**
	 * Get the treatment finding identifier.
	 *
	 * @return string
	 */
	public static function get_finding_id(): string {
		return self::$slug;
	}

	/**
	 * Get the treatment risk level.
	 *
	 * @return string
	 */
	public static function get_risk_level(): string {
		return 'high';
	}

	/**
	 * Convert all core WordPress tables to utf8mb4_unicode_ci.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		global $wpdb;

		/*
		 * Charset and collation remediation is unavoidably a $wpdb task.
		 * Core WordPress APIs can read and write content, but they do not expose a high-level
		 * function for inspecting table metadata or issuing ALTER TABLE ... CONVERT TO CHARACTER SET
		 * statements. This treatment is operating at the schema layer, so SHOW TABLE STATUS and raw
		 * ALTER TABLE statements are the correct primitives.
		 */

		$tables    = self::get_core_tables();
		$backup    = array();
		$converted = array();
		$failures  = array();

		foreach ( $tables as $table ) {
			$table_name = self::require_schema_identifier( (string) $table );
			if ( '' === $table_name ) {
				$failures[] = sprintf( 'Invalid table identifier: %s', (string) $table );
				continue;
			}

			$status = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- SHOW TABLE STATUS is required for bounded schema inspection before conversion.
				$wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table_name ),
				ARRAY_A
			);

			if ( empty( $status ) ) {
				continue; // Table does not exist; skip.
			}

			$current_collation = $status['Collation'] ?? '';
			$current_charset   = $current_collation
				? explode( '_', $current_collation )[0]
				: '';

			// Skip tables already using the target charset/collation.
			if ( self::TARGET_COLLATION === $current_collation ) {
				continue;
			}

			// Back up original state.
			$backup[ $table_name ] = array(
				'charset'   => $current_charset,
				'collation' => $current_collation,
			);

			$target_charset   = self::require_schema_identifier( self::TARGET_CHARSET );
			$target_collation = self::require_schema_identifier( self::TARGET_COLLATION );
			if ( '' === $target_charset || '' === $target_collation ) {
				$failures[] = sprintf( 'Invalid target charset/collation for `%s`.', $table_name );
				unset( $backup[ $table_name ] );
				continue;
			}

			$sql    = sprintf(
				'ALTER TABLE `%s` CONVERT TO CHARACTER SET %s COLLATE %s',
				$table_name,
				$target_charset,
				$target_collation
			);
			$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- ALTER TABLE requires validated DDL fragments for this bounded schema migration.

			if ( false === $result ) {
				$failures[] = sprintf( '`%s`: %s', $table_name, $wpdb->last_error );
				unset( $backup[ $table_name ] );
			} else {
				$converted[] = $table_name;
			}
		}

		if ( ! empty( $backup ) ) {
			update_option( self::OPTION_KEY, $backup, false );
		}

		if ( empty( $converted ) && empty( $failures ) ) {
			return array(
				'success' => true,
				'message' => __( 'All checked tables are already using utf8mb4_unicode_ci — no changes needed.', 'wpshadow' ),
			);
		}

		if ( ! empty( $failures ) ) {
			$converted_summary = ! empty( $converted ) ? implode( ', ', $converted ) : 'none';

			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: 1: count, 2: converted list, 3: failure list */
					__( 'Converted %1$d table(s) to utf8mb4. Failed: %2$s. Errors: %3$s', 'wpshadow' ),
					count( $converted ),
					$converted_summary,
					implode( '; ', $failures )
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: list of converted tables */
				__( 'Successfully converted %1$d table(s) to utf8mb4_unicode_ci: %2$s.', 'wpshadow' ),
				count( $converted ),
				implode( ', ', $converted )
			),
		);
	}

	/**
	 * Revert the charset/collation of converted tables to their original value.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		global $wpdb;

		/*
		 * The undo path keeps the same low-level approach for the same reason: once table charset
		 * and collation have changed, only schema-level SQL can restore the prior definition.
		 */

		$backup = (array) get_option( self::OPTION_KEY, array() );

		if ( empty( $backup ) ) {
			return array(
				'success' => false,
				'message' => __( 'No backup charset data found — cannot restore. Tables are still utf8mb4 (which is safe to leave as-is).', 'wpshadow' ),
			);
		}

		$reverted = array();
		$failures = array();

		foreach ( $backup as $table => $original ) {
			$table_name = self::require_schema_identifier( (string) $table );
			$charset    = self::require_schema_identifier( (string) ( $original['charset'] ?? '' ) );
			$collation  = self::require_schema_identifier( (string) ( $original['collation'] ?? '' ) );

			if ( '' === $table_name || '' === $charset || '' === $collation ) {
				continue;
			}

			$sql    = sprintf(
				'ALTER TABLE `%s` CONVERT TO CHARACTER SET %s COLLATE %s',
				$table_name,
				$charset,
				$collation
			);
			$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- ALTER TABLE requires validated DDL fragments for this bounded schema rollback.

			if ( false === $result ) {
				$failures[] = sprintf( '`%s`', $table_name );
			} else {
				$reverted[] = sprintf( '`%s` → %s/%s', $table_name, $charset, $collation );
			}
		}

		delete_option( self::OPTION_KEY );

		$warning = __( 'WARNING: Reverting from utf8mb4 to utf8 may silently discard any 4-byte characters (emoji, certain symbols) stored during the utf8mb4 period.', 'wpshadow' );

		if ( ! empty( $failures ) ) {
			$reverted_summary = ! empty( $reverted ) ? implode( ', ', $reverted ) : 'none';

			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: 1: reverted list, 2: failure list, 3: warning */
					__( 'Reverted: %1$s. Failed: %2$s. %3$s', 'wpshadow' ),
					$reverted_summary,
					implode( ', ', $failures ),
					$warning
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: 1: reverted list, 2: warning message */
				__( 'Tables reverted: %1$s. %2$s', 'wpshadow' ),
				implode( ', ', $reverted ),
				$warning
			),
		);
	}

	// =========================================================================
	// Internal helpers
	// =========================================================================

	/**
	 * Return the list of WordPress core table names to convert.
	 *
	 * @return list<string>
	 */
	private static function get_core_tables(): array {
		global $wpdb;

		return array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->terms,
			$wpdb->term_taxonomy,
			$wpdb->term_relationships,
			$wpdb->termmeta,
			$wpdb->options,
			$wpdb->links,
		);
	}
}

<?php
/**
 * Treatment: InnoDB Storage Engine Used
 *
 * Converts any WordPress core tables that are not using the InnoDB storage
 * engine. InnoDB provides ACID transactions, row-level locking, crash
 * recovery, and full-text search. MyISAM (and other older engines) lack those
 * capabilities and have been deprecated in MySQL 8+.
 *
 * What `apply()` does:
 *  1. Runs `SHOW TABLE STATUS` to determine the current engine for each
 *     WordPress core table.
 *  2. Stores the original engine name for each non-InnoDB table.
 *  3. Runs `ALTER TABLE ... ENGINE=InnoDB` for each non-InnoDB table.
 *
 * What `undo()` does:
 *  Reverts each converted table back to the engine recorded during apply().
 *  Note that downgrading from InnoDB to MyISAM is generally not recommended
 *  and the treatment returns a warning accordingly.
 *
 * Risk level: high (table-level rebuild, potentially slow on large tables)
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

/**
 * Converts all WordPress core tables to the InnoDB storage engine.
 */
class Treatment_Innodb_Storage_Engine_Used extends Treatment_Base {

	use Database_Schema_Helpers;

	/**
	 * Treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'innodb-storage-engine-used';

	const OPTION_KEY    = 'wpshadow_innodb_engine_backup';
	const TARGET_ENGINE = 'InnoDB';

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
	 * Convert all non-InnoDB core tables to InnoDB.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		global $wpdb;

		/*
		 * Storage-engine checks and conversions must use $wpdb directly.
		 * WordPress does not provide an abstraction for SHOW TABLE STATUS or ALTER TABLE ... ENGINE,
		 * because engine selection is database-schema administration rather than content management.
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

			$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- SHOW TABLE STATUS is required for bounded schema inspection before engine conversion.
				$wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table_name ),
				ARRAY_A
			);

			if ( empty( $row ) ) {
				continue; // Table not found; skip.
			}

			$engine = $row['Engine'] ?? '';

			if ( self::TARGET_ENGINE === $engine ) {
				continue; // Already InnoDB.
			}

			$backup[ $table_name ] = $engine;
			$target_engine         = self::require_schema_identifier( self::TARGET_ENGINE );

			if ( '' === $target_engine ) {
				$failures[] = sprintf( 'Invalid target engine for `%s`.', $table_name );
				unset( $backup[ $table_name ] );
				continue;
			}

			$result = $wpdb->query( sprintf( 'ALTER TABLE `%s` ENGINE=%s', $table_name, $target_engine ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- ALTER TABLE requires validated DDL fragments for this bounded schema migration.

			if ( false === $result ) {
				$failures[] = sprintf( '`%s` (%s): %s', $table_name, $engine, $wpdb->last_error );
				unset( $backup[ $table_name ] );
			} else {
				$converted[] = sprintf( '`%s` (%s → InnoDB)', $table_name, $engine );
			}
		}

		if ( ! empty( $backup ) ) {
			update_option( self::OPTION_KEY, $backup, false );
		}

		if ( empty( $converted ) && empty( $failures ) ) {
			return array(
				'success' => true,
				'message' => __( 'All core WordPress tables are already using InnoDB — no changes needed.', 'wpshadow' ),
			);
		}

		if ( ! empty( $failures ) ) {
			$converted_summary = ! empty( $converted ) ? implode( ', ', $converted ) : 'none';

			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: 1: converted list, 2: failures */
					__( 'Converted: %1$s. Failed: %2$s.', 'wpshadow' ),
					$converted_summary,
					implode( '; ', $failures )
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: list of converted tables */
				__( 'Successfully converted %1$d table(s) to InnoDB: %2$s.', 'wpshadow' ),
				count( $converted ),
				implode( ', ', $converted )
			),
		);
	}

	/**
	 * Revert converted tables to their original storage engine.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		global $wpdb;

		/*
		 * Reverting table engines is the same class of schema operation. There is no WordPress core
		 * helper for this, so direct SQL remains the correct and explicit implementation.
		 */

		$backup = (array) get_option( self::OPTION_KEY, array() );

		if ( empty( $backup ) ) {
			return array(
				'success' => false,
				'message' => __( 'No backup engine data found. Tables remain on InnoDB, which is the safe and recommended choice.', 'wpshadow' ),
			);
		}

		$reverted = array();
		$failures = array();

		foreach ( $backup as $table => $original_engine ) {
			$table_name  = self::require_schema_identifier( (string) $table );
			$engine_name = self::require_schema_identifier( (string) $original_engine );

			if ( '' === $table_name || '' === $engine_name ) {
				continue;
			}

			$result = $wpdb->query( sprintf( 'ALTER TABLE `%s` ENGINE=%s', $table_name, $engine_name ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- ALTER TABLE requires validated DDL fragments for this bounded schema rollback.

			if ( false === $result ) {
				$failures[] = sprintf( '`%s`', $table_name );
			} else {
				$reverted[] = sprintf( '`%s` → %s', $table_name, $engine_name );
			}
		}

		delete_option( self::OPTION_KEY );

		$warning = __( 'Note: Reverting to MyISAM or other legacy engines is not recommended. InnoDB provides better data integrity and is required by many modern plugins.', 'wpshadow' );

		if ( ! empty( $failures ) ) {
			$reverted_summary = ! empty( $reverted ) ? implode( ', ', $reverted ) : 'none';

			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: 1: reverted, 2: failures, 3: warning */
					__( 'Reverted: %1$s. Failed to revert: %2$s. %3$s', 'wpshadow' ),
					$reverted_summary,
					implode( ', ', $failures ),
					$warning
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: 1: list, 2: warning */
				__( 'Reverted: %1$s. %2$s', 'wpshadow' ),
				implode( ', ', $reverted ),
				$warning
			),
		);
	}

	// =========================================================================
	// Internal helpers
	// =========================================================================

	/**
	 * Return the WordPress core tables to check.
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

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
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts all WordPress core tables to the InnoDB storage engine.
 */
class Treatment_Innodb_Storage_Engine_Used extends Treatment_Base {

	/** @var string */
	protected static $slug = 'innodb-storage-engine-used';

	const OPTION_KEY     = 'wpshadow_innodb_engine_backup';
	const TARGET_ENGINE  = 'InnoDB';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

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

		$tables    = self::get_core_tables();
		$backup    = [];
		$converted = [];
		$failures  = [];

		foreach ( $tables as $table ) {
			$row = $wpdb->get_row(
				$wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table ),
				ARRAY_A
			);

			if ( empty( $row ) ) {
				continue; // Table not found; skip.
			}

			$engine = $row['Engine'] ?? '';

			if ( self::TARGET_ENGINE === $engine ) {
				continue; // Already InnoDB.
			}

			$backup[ $table ] = $engine;

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->query( "ALTER TABLE `{$table}` ENGINE=" . self::TARGET_ENGINE );

			if ( false === $result ) {
				$failures[] = sprintf( '`%s` (%s): %s', $table, $engine, $wpdb->last_error );
				unset( $backup[ $table ] );
			} else {
				$converted[] = sprintf( '`%s` (%s → InnoDB)', $table, $engine );
			}
		}

		if ( ! empty( $backup ) ) {
			update_option( self::OPTION_KEY, $backup, false );
		}

		if ( empty( $converted ) && empty( $failures ) ) {
			return [
				'success' => true,
				'message' => __( 'All core WordPress tables are already using InnoDB — no changes needed.', 'wpshadow' ),
			];
		}

		if ( ! empty( $failures ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: 1: converted list, 2: failures */
					__( 'Converted: %1$s. Failed: %2$s.', 'wpshadow' ),
					implode( ', ', $converted ) ?: 'none',
					implode( '; ', $failures )
				),
			];
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %s: list of converted tables */
				__( 'Successfully converted %d table(s) to InnoDB: %s.', 'wpshadow' ),
				count( $converted ),
				implode( ', ', $converted )
			),
		];
	}

	/**
	 * Revert converted tables to their original storage engine.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		global $wpdb;

		$backup = (array) get_option( self::OPTION_KEY, [] );

		if ( empty( $backup ) ) {
			return [
				'success' => false,
				'message' => __( 'No backup engine data found. Tables remain on InnoDB, which is the safe and recommended choice.', 'wpshadow' ),
			];
		}

		$reverted = [];
		$failures = [];

		foreach ( $backup as $table => $original_engine ) {
			if ( '' === $original_engine ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->query( "ALTER TABLE `{$table}` ENGINE={$original_engine}" );

			if ( false === $result ) {
				$failures[] = sprintf( '`%s`', $table );
			} else {
				$reverted[] = sprintf( '`%s` → %s', $table, $original_engine );
			}
		}

		delete_option( self::OPTION_KEY );

		$warning = __( 'Note: Reverting to MyISAM or other legacy engines is not recommended. InnoDB provides better data integrity and is required by many modern plugins.', 'wpshadow' );

		if ( ! empty( $failures ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: 1: reverted, 2: failures, 3: warning */
					__( 'Reverted: %1$s. Failed to revert: %2$s. %3$s', 'wpshadow' ),
					implode( ', ', $reverted ) ?: 'none',
					implode( ', ', $failures ),
					$warning
				),
			];
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: 1: list, 2: warning */
				__( 'Reverted: %1$s. %2$s', 'wpshadow' ),
				implode( ', ', $reverted ),
				$warning
			),
		];
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

		return [
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
		];
	}
}

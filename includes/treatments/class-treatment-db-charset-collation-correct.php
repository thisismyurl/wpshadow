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
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts WordPress core tables to utf8mb4_unicode_ci.
 */
class Treatment_Db_Charset_Collation_Correct extends Treatment_Base {

	/** @var string */
	protected static $slug = 'db-charset-collation-correct';

	const OPTION_KEY       = 'wpshadow_db_charset_backup';
	const TARGET_CHARSET   = 'utf8mb4';
	const TARGET_COLLATION = 'utf8mb4_unicode_ci';

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
	 * Convert all core WordPress tables to utf8mb4_unicode_ci.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		global $wpdb;

		$tables   = self::get_core_tables();
		$backup   = [];
		$converted = [];
		$failures = [];

		foreach ( $tables as $table ) {
			$status = $wpdb->get_row(
				$wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table ),
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
			$backup[ $table ] = [
				'charset'   => $current_charset,
				'collation' => $current_collation,
			];

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql    = sprintf(
				'ALTER TABLE `%s` CONVERT TO CHARACTER SET %s COLLATE %s',
				$table,
				self::TARGET_CHARSET,
				self::TARGET_COLLATION
			);
			$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( false === $result ) {
				$failures[] = sprintf( '`%s`: %s', $table, $wpdb->last_error );
				unset( $backup[ $table ] );
			} else {
				$converted[] = $table;
			}
		}

		if ( ! empty( $backup ) ) {
			update_option( self::OPTION_KEY, $backup, false );
		}

		if ( empty( $converted ) && empty( $failures ) ) {
			return [
				'success' => true,
				'message' => __( 'All checked tables are already using utf8mb4_unicode_ci — no changes needed.', 'wpshadow' ),
			];
		}

		if ( ! empty( $failures ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: 1: count, 2: converted list, 3: failure list */
					__( 'Converted %1$d table(s) to utf8mb4. Failed: %2$s. Errors: %3$s', 'wpshadow' ),
					count( $converted ),
					implode( ', ', $converted ) ?: 'none',
					implode( '; ', $failures )
				),
			];
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %s: list of converted tables */
				__( 'Successfully converted %d table(s) to utf8mb4_unicode_ci: %s.', 'wpshadow' ),
				count( $converted ),
				implode( ', ', $converted )
			),
		];
	}

	/**
	 * Revert the charset/collation of converted tables to their original value.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		global $wpdb;

		$backup = (array) get_option( self::OPTION_KEY, [] );

		if ( empty( $backup ) ) {
			return [
				'success' => false,
				'message' => __( 'No backup charset data found — cannot restore. Tables are still utf8mb4 (which is safe to leave as-is).', 'wpshadow' ),
			];
		}

		$reverted = [];
		$failures = [];

		foreach ( $backup as $table => $original ) {
			$charset   = $original['charset']   ?? '';
			$collation = $original['collation'] ?? '';

			if ( '' === $charset || '' === $collation ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql    = sprintf(
				'ALTER TABLE `%s` CONVERT TO CHARACTER SET %s COLLATE %s',
				$table,
				$charset,
				$collation
			);
			$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( false === $result ) {
				$failures[] = sprintf( '`%s`', $table );
			} else {
				$reverted[] = sprintf( '`%s` → %s/%s', $table, $charset, $collation );
			}
		}

		delete_option( self::OPTION_KEY );

		$warning = __( 'WARNING: Reverting from utf8mb4 to utf8 may silently discard any 4-byte characters (emoji, certain symbols) stored during the utf8mb4 period.', 'wpshadow' );

		if ( ! empty( $failures ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: 1: reverted list, 2: failure list, 3: warning */
					__( 'Reverted: %1$s. Failed: %2$s. %3$s', 'wpshadow' ),
					implode( ', ', $reverted ) ?: 'none',
					implode( ', ', $failures ),
					$warning
				),
			];
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: 1: reverted list, 2: warning message */
				__( 'Tables reverted: %1$s. %2$s', 'wpshadow' ),
				implode( ', ', $reverted ),
				$warning
			),
		];
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

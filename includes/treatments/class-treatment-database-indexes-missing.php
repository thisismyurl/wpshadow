<?php
/**
 * Treatment: Database Indexes Missing
 *
 * Inspects the same expected indexes that the diagnostic checks and runs
 * a CREATE INDEX statement for each one that is absent. The treatment stores
 * the list of indexes it added so that undo() can drop them precisely.
 *
 * Expected indexes (matching the diagnostic):
 *  - wp_posts:             post_name, type_status_date, post_parent, post_author
 *  - wp_postmeta:          post_id, meta_key
 *  - wp_comments:          comment_post_ID, comment_approved_date_gmt, comment_parent
 *  - wp_term_relationships: term_taxonomy_id
 *  - wp_usermeta:          user_id, meta_key
 *
 * Most of these indexes are created by WordPress's dbDelta() at install time.
 * They can go missing after manual table modifications or faulty plugin
 * migrations. Adding them back is non-destructive and does not alter data.
 *
 * Risk level: medium (schema change)
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
 * Adds missing indexes to core WordPress tables.
 */
class Treatment_Database_Indexes_Missing extends Treatment_Base {

	/** @var string */
	protected static $slug = 'database-indexes-missing';

	const OPTION_KEY = 'wpshadow_added_db_indexes';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'medium';
	}

	/**
	 * Add each missing index to its table.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		global $wpdb;

		$expected = self::get_expected_indexes();
		$added    = [];
		$failures = [];

		foreach ( $expected as $table => $indexes ) {
			// Get existing index names for this table.
			$existing_rows = $wpdb->get_results(
				$wpdb->prepare( 'SHOW INDEX FROM %i', $table ),
				ARRAY_A
			);
			$existing_names = array_column( (array) $existing_rows, 'Key_name' );

			foreach ( $indexes as $index_name => $definition ) {
				if ( in_array( $index_name, $existing_names, true ) ) {
					continue; // Already present.
				}

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$sql    = "ALTER TABLE `{$table}` ADD INDEX `{$index_name}` ({$definition})";
				$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				if ( false === $result ) {
					$failures[] = sprintf( '`%s`.`%s`: %s', $table, $index_name, $wpdb->last_error );
				} else {
					$added[] = [ 'table' => $table, 'index' => $index_name ];
				}
			}
		}

		if ( ! empty( $added ) ) {
			// Store what we added so undo() can remove exactly those indexes.
			$previous = (array) get_option( self::OPTION_KEY, [] );
			update_option( self::OPTION_KEY, array_merge( $previous, $added ), false );
		}

		if ( empty( $added ) && empty( $failures ) ) {
			return [
				'success' => true,
				'message' => __( 'All expected indexes are already present — no changes were made.', 'wpshadow' ),
			];
		}

		$added_summary = implode( ', ', array_map(
			static function ( $a ): string {
				return sprintf( '`%s`.`%s`', $a['table'], $a['index'] );
			},
			$added
		) );

		if ( ! empty( $failures ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: 1: added list, 2: failure list */
					__( 'Added: %1$s. Failed to add: %2$s.', 'wpshadow' ),
					$added_summary ?: 'none',
					implode( '; ', $failures )
				),
			];
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %s: comma-separated list of index.table pairs */
				__( 'Successfully added %d missing index(es): %s.', 'wpshadow' ),
				count( $added ),
				$added_summary
			),
		];
	}

	/**
	 * Drop only the indexes that this treatment added.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		global $wpdb;

		$added = (array) get_option( self::OPTION_KEY, [] );

		if ( empty( $added ) ) {
			return [
				'success' => false,
				'message' => __( 'No record of indexes added by WPShadow — cannot undo. Indexes (if present) must be removed manually.', 'wpshadow' ),
			];
		}

		$dropped  = [];
		$failures = [];

		foreach ( $added as $item ) {
			$table = $item['table'] ?? '';
			$index = $item['index'] ?? '';

			if ( '' === $table || '' === $index ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->query( "ALTER TABLE `{$table}` DROP INDEX `{$index}`" );

			if ( false === $result ) {
				$failures[] = sprintf( '`%s`.`%s`: %s', $table, $index, $wpdb->last_error );
			} else {
				$dropped[] = sprintf( '`%s`.`%s`', $table, $index );
			}
		}

		delete_option( self::OPTION_KEY );

		if ( ! empty( $failures ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: 1: dropped list, 2: failure list */
					__( 'Dropped: %1$s. Failed to drop: %2$s.', 'wpshadow' ),
					implode( ', ', $dropped ) ?: 'none',
					implode( '; ', $failures )
				),
			];
		}

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %s: list of removed indexes */
				__( 'Removed %d index(es) added by WPShadow: %s.', 'wpshadow' ),
				count( $dropped ),
				implode( ', ', $dropped )
			),
		];
	}

	// =========================================================================
	// Internal helpers
	// =========================================================================

	/**
	 * Return the expected indexes for each WordPress core table.
	 *
	 * The key is the index name; the value is the column expression used in
	 * CREATE INDEX.
	 *
	 * @return array<string, array<string, string>>
	 */
	private static function get_expected_indexes(): array {
		global $wpdb;

		return [
			$wpdb->posts => [
				'post_name'        => '`post_name`(20)',
				'type_status_date' => '`post_type`, `post_status`, `post_date`, `ID`',
				'post_parent'      => '`post_parent`',
				'post_author'      => '`post_author`',
			],
			$wpdb->postmeta => [
				'post_id'  => '`post_id`',
				'meta_key' => '`meta_key`(191)',
			],
			$wpdb->comments => [
				'comment_post_ID'           => '`comment_post_ID`',
				'comment_approved_date_gmt' => '`comment_approved`, `comment_date_gmt`',
				'comment_parent'            => '`comment_parent`',
			],
			$wpdb->term_relationships => [
				'term_taxonomy_id' => '`term_taxonomy_id`',
			],
			$wpdb->usermeta => [
				'user_id'  => '`user_id`',
				'meta_key' => '`meta_key`(191)',
			],
		];
	}
}

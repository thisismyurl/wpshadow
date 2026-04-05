<?php
/**
 * Treatment: Delete Orphaned Term Relationships
 *
 * Deletes rows in wp_term_relationships whose object_id has no corresponding
 * post in wp_posts. These accumulate when posts are removed by direct DB
 * delete rather than through the WordPress API.
 *
 * Risk level: moderate — deletes database rows. Backup before running.
 * Undo is not available; deleted rows require a database restore.
 *
 * @package WPShadow
 * @since   0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes orphaned term relationship rows from wp_term_relationships.
 */
class Treatment_Orphaned_Term_Relationships extends Treatment_Base {

	/**
	 * Treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-term-relationships';

	/**
	 * Get the treatment risk level.
	 *
	 * @return string
	 */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Delete orphaned term relationship rows.
	 *
	 * @return array
	 */
	public static function apply() {
		global $wpdb;

		/*
		 * Term relationships are stored in a pure relational join table, and WordPress does not offer
		 * a single helper for removing every relationship whose object no longer exists. A JOIN delete
		 * through $wpdb is therefore the clearest and most efficient way to repair this table.
		 */

		$deleted = (int) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional bulk cleanup treatment against core taxonomy tables.
			$wpdb->prepare(
				'DELETE tr FROM %i tr
				 LEFT JOIN %i p ON p.ID = tr.object_id
				 WHERE p.ID IS NULL',
				$wpdb->term_relationships,
				$wpdb->posts
			)
		);

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of orphaned term relationship rows deleted. */
				_n(
					'%d orphaned term relationship deleted from the database.',
					'%d orphaned term relationships deleted from the database.',
					$deleted,
					'wpshadow'
				),
				$deleted
			),
			'details' => array( 'rows_deleted' => $deleted ),
		);
	}

	/**
	 * Undo is not available for database row deletions.
	 *
	 * @return array
	 */
	public static function undo() {
		return array(
			'success' => false,
			'message' => __( 'Deleted database rows cannot be automatically restored. Recover from a database backup if needed.', 'wpshadow' ),
		);
	}
}

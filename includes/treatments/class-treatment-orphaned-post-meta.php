<?php
/**
 * Treatment: Delete Orphaned Post Meta
 *
 * Deletes rows in wp_postmeta whose post_id has no corresponding entry
 * in wp_posts. These rows accumulate when posts are deleted without
 * triggering the wp_delete_post action (e.g. direct DB deletes, plugin bugs).
 *
 * Risk level: moderate — deletes database rows. Backup before running.
 * Undo is not available; deleted rows require a database restore.
 *
 * @package WPShadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes orphaned post meta rows from wp_postmeta.
 */
class Treatment_Orphaned_Post_Meta extends Treatment_Base {

	/**
	 * Treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-post-meta';

	/**
	 * Get the treatment risk level.
	 *
	 * @return string
	 */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Delete orphaned postmeta rows.
	 *
	 * @return array
	 */
	public static function apply() {
		global $wpdb;

		/*
		 * Orphan detection is intentionally done with a single JOIN delete.
		 * WordPress has APIs to delete known metadata rows, but it does not have a native function to
		 * express "delete every postmeta row whose parent post no longer exists" without first loading
		 * the orphan set into PHP. The JOIN is both more accurate and more efficient.
		 */

		$deleted = (int) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional bulk cleanup treatment against core metadata tables.
			$wpdb->prepare(
				'DELETE pm FROM %i pm
				 LEFT JOIN %i p ON p.ID = pm.post_id
				 WHERE p.ID IS NULL',
				$wpdb->postmeta,
				$wpdb->posts
			)
		);

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of orphaned post meta rows deleted. */
				_n(
					'%d orphaned post meta row deleted from the database.',
					'%d orphaned post meta rows deleted from the database.',
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

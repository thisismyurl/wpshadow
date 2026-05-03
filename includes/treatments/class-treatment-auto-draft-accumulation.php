<?php
/**
 * Treatment: Delete Auto-Draft Accumulation
 *
 * Deletes stale auto-draft rows from wp_posts and cleans up orphaned postmeta
 * attached to those deleted rows.
 *
 * Risk level: moderate — deletes database rows. Backup before running.
 * Undo is not available; deleted rows require a database restore.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes accumulated auto-draft rows from wp_posts.
 */
class Treatment_Auto_Draft_Accumulation extends Treatment_Base {

	/**
	 * Treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'auto-draft-accumulation';

	/**
	 * Get the treatment risk level.
	 *
	 * @return string
	 */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Delete auto-draft posts and their now-orphaned postmeta rows.
	 *
	 * @return array
	 */
	public static function apply() {
		global $wpdb;

		/*
		 * This treatment keeps direct SQL because it is performing bulk row cleanup with relational
		 * guarantees. A loop over wp_delete_post() would invoke hooks and per-post side effects for
		 * each auto-draft, which is much slower and can produce behavior unrelated to the narrow goal
		 * of clearing abandoned placeholder rows. The orphaned postmeta cleanup also needs a LEFT JOIN
		 * that WordPress does not expose as a higher-level API.
		 */

		$deleted_posts = (int) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional bulk cleanup treatment against a core table.
			$wpdb->prepare(
				'DELETE FROM %i WHERE post_status = %s',
				$wpdb->posts,
				'auto-draft'
			)
		);

		$deleted_meta = (int) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional bulk cleanup treatment against core metadata tables.
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
			/* translators: 1: number of auto-draft posts removed, 2: number of orphaned postmeta rows removed */
			'message' => sprintf(
				/* translators: 1: number of auto-draft posts removed, 2: number of orphaned postmeta rows removed. */
				__( 'Removed %1$d auto-draft post(s) and %2$d orphaned postmeta row(s).', 'thisismyurl-shadow' ),
				$deleted_posts,
				$deleted_meta
			),
			'details' => array(
				'auto_drafts_deleted' => $deleted_posts,
				'postmeta_deleted'    => $deleted_meta,
			),
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
			'message' => __( 'Deleted database rows cannot be automatically restored. Recover from a database backup if needed.', 'thisismyurl-shadow' ),
		);
	}
}

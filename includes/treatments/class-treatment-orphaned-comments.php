<?php
/**
 * Treatment: Delete Orphaned Comments
 *
 * Deletes comments whose parent post no longer exists in wp_posts, and
 * deletes spam comments that have been sitting in the spam queue.
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
 * Removes orphaned comments (post deleted) and spam comment backlog.
 */
class Treatment_Orphaned_Comments extends Treatment_Base {

	/**
	 * Treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-comments';

	/**
	 * Get the treatment risk level.
	 *
	 * @return string
	 */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Delete orphaned and spam comments from the database.
	 *
	 * @return array
	 */
	public static function apply() {
		global $wpdb;

		/*
		 * Comment cleanup uses $wpdb deliberately because it needs set-based deletes across comments,
		 * commentmeta, and missing parent posts. Core comment APIs operate on known IDs one at a time;
		 * they do not provide a bulk "remove every orphaned comment and its meta" primitive.
		 */

		// Delete comments whose parent post no longer exists.
		$orphaned_deleted = (int) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional bulk cleanup treatment against core comment tables.
			$wpdb->prepare(
				'DELETE c FROM %i c
				 LEFT JOIN %i p ON p.ID = c.comment_post_ID
				 WHERE p.ID IS NULL',
				$wpdb->comments,
				$wpdb->posts
			)
		);

		// Delete associated comment meta for orphaned comments first (integrity).
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional bulk cleanup treatment against core comment metadata tables.
			$wpdb->prepare(
				'DELETE cm FROM %i cm
				 LEFT JOIN %i c ON c.comment_ID = cm.comment_id
				 WHERE c.comment_ID IS NULL',
				$wpdb->commentmeta,
				$wpdb->comments
			)
		);

		// Delete spam comments.
		$spam_deleted = (int) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional bulk cleanup treatment against core comment tables.
			$wpdb->prepare(
				'DELETE FROM %i
				 WHERE comment_approved = %s',
				$wpdb->comments,
				'spam'
			)
		);

		$total = $orphaned_deleted + $spam_deleted;

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: 1: number of orphaned comments deleted, 2: number of spam comments deleted. */
				__( 'Removed %1$d orphaned comment(s) and %2$d spam comment(s) from the database.', 'wpshadow' ),
				$orphaned_deleted,
				$spam_deleted
			),
			'details' => array(
				'orphaned_deleted' => $orphaned_deleted,
				'spam_deleted'     => $spam_deleted,
				'total_deleted'    => $total,
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
			'message' => __( 'Deleted database rows cannot be automatically restored. Recover from a database backup if needed.', 'wpshadow' ),
		);
	}
}

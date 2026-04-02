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
 * @since   0.6093.1200
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
	 * @var string
	 */
	protected static $slug = 'orphaned-comments';

	/** @return string */
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

		// Delete comments whose parent post no longer exists.
		$orphaned_deleted = (int) $wpdb->query(
			"DELETE c FROM {$wpdb->comments} c
			 LEFT JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID
			 WHERE p.ID IS NULL"
		);

		// Delete associated comment meta for orphaned comments first (integrity).
		$wpdb->query(
			"DELETE cm FROM {$wpdb->commentmeta} cm
			 LEFT JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id
			 WHERE c.comment_ID IS NULL"
		);

		// Delete spam comments.
		$spam_deleted = (int) $wpdb->query(
			"DELETE FROM {$wpdb->comments}
			 WHERE comment_approved = 'spam'"
		);

		$total = $orphaned_deleted + $spam_deleted;

		return array(
			'success' => true,
			/* translators: 1: orphaned count, 2: spam count */
			'message' => sprintf(
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

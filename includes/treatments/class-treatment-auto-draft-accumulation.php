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
 * Removes accumulated auto-draft rows from wp_posts.
 */
class Treatment_Auto_Draft_Accumulation extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'auto-draft-accumulation';

	/** @return string */
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

		$deleted_posts = (int) $wpdb->query(
			"DELETE FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"
		);

		$deleted_meta = (int) $wpdb->query(
			"DELETE pm FROM {$wpdb->postmeta} pm
			 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE p.ID IS NULL"
		);

		return array(
			'success' => true,
			/* translators: 1: number of auto-draft posts removed, 2: number of orphaned postmeta rows removed */
			'message' => sprintf(
				__( 'Removed %1$d auto-draft post(s) and %2$d orphaned postmeta row(s).', 'wpshadow' ),
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
			'message' => __( 'Deleted database rows cannot be automatically restored. Recover from a database backup if needed.', 'wpshadow' ),
		);
	}
}

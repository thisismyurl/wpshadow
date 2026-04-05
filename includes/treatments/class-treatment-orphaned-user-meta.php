<?php
/**
 * Treatment: Delete Orphaned User Meta
 *
 * Deletes rows in wp_usermeta whose user_id has no corresponding user in
 * wp_users.
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
 * Removes orphaned rows from wp_usermeta.
 */
class Treatment_Orphaned_User_Meta extends Treatment_Base {

	/**
	 * Treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-user-meta';

	/**
	 * Get the treatment risk level.
	 *
	 * @return string
	 */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Delete orphaned usermeta rows.
	 *
	 * @return array
	 */
	public static function apply() {
		global $wpdb;

		/*
		 * User-meta orphan cleanup is another case where the database relation matters more than the
		 * object API. delete_user_meta() can remove known keys for a known user, but it cannot express
		 * "remove every usermeta row whose owning user record is gone" without first scanning the table.
		 */

		$deleted = (int) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional bulk cleanup treatment against core user metadata tables.
			$wpdb->prepare(
				'DELETE um FROM %i um
				 LEFT JOIN %i u ON um.user_id = u.ID
				 WHERE u.ID IS NULL',
				$wpdb->usermeta,
				$wpdb->users
			)
		);

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of orphaned user meta rows deleted. */
				_n(
					'%d orphaned user meta row deleted from the database.',
					'%d orphaned user meta rows deleted from the database.',
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

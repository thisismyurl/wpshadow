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
	 * @var string
	 */
	protected static $slug = 'orphaned-user-meta';

	/** @return string */
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

		$deleted = (int) $wpdb->query(
			"DELETE um FROM {$wpdb->usermeta} um
			 LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
			 WHERE u.ID IS NULL"
		);

		return array(
			'success' => true,
			/* translators: %d: number of rows deleted */
			'message' => sprintf(
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

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
	 * @var string
	 */
	protected static $slug = 'orphaned-term-relationships';

	/** @return string */
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

		$deleted = (int) $wpdb->query(
			"DELETE tr FROM {$wpdb->term_relationships} tr
			 LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id
			 WHERE p.ID IS NULL"
		);

		return array(
			'success' => true,
			/* translators: %d: number of rows deleted */
			'message' => sprintf(
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

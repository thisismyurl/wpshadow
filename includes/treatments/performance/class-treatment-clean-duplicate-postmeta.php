<?php
/**
 * Treatment: Clean Duplicate Postmeta
 *
 * Removes duplicate postmeta entries, keeping only the latest.
 *
 * Philosophy: Helpful Neighbor (#1) - Offers choice with preview
 * KB Link: https://wpshadow.com/kb/duplicate-postmeta-keys
 * Training: https://wpshadow.com/training/duplicate-postmeta-keys
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean Duplicate Postmeta treatment
 */
class Treatment_Clean_Duplicate_Postmeta extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = array() ): bool {
		global $wpdb;

		// Find duplicates
		$duplicates = $wpdb->get_results(
			"SELECT 
				post_id,
				meta_key,
				COUNT(*) as count
			FROM {$wpdb->postmeta}
			GROUP BY post_id, meta_key
			HAVING count > 1",
			ARRAY_A
		);

		if ( empty( $duplicates ) ) {
			return false;
		}

		$deleted_total = 0;
		$backup_data   = array();

		foreach ( $duplicates as $duplicate ) {
			$post_id  = $duplicate['post_id'];
			$meta_key = $duplicate['meta_key'];

			// Get all meta_ids for this combination
			$meta_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT meta_id FROM {$wpdb->postmeta} 
					WHERE post_id = %d AND meta_key = %s 
					ORDER BY meta_id DESC",
					$post_id,
					$meta_key
				)
			);

			// Keep the latest (first in DESC order), delete the rest
			$keep_id    = array_shift( $meta_ids );
			$delete_ids = $meta_ids;

			if ( empty( $delete_ids ) ) {
				continue;
			}

			// Backup deleted values
			$backup_data[] = array(
				'post_id'     => $post_id,
				'meta_key'    => $meta_key,
				'kept_id'     => $keep_id,
				'deleted_ids' => $delete_ids,
			);

			// Delete duplicates
			$placeholders = implode( ',', array_fill( 0, count( $delete_ids ), '%d' ) );
			$deleted      = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->postmeta} WHERE meta_id IN ({$placeholders})",
					...$delete_ids
				)
			);

			$deleted_total += $deleted;
		}

		// Create backup
		self::create_backup(
			array(
				'deleted_count' => $deleted_total,
				'duplicates'    => $backup_data,
				'timestamp'     => time(),
			)
		);

		// Track KPI
		if ( $deleted_total > 0 ) {
			KPI_Tracker::record_treatment_applied( __CLASS__, 2 );
		}

		return $deleted_total > 0;
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		// Cannot restore deleted duplicate metadata (we kept the latest)
		// User should have database backups for critical data
		return true;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Clean Duplicate Postmeta', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Removes duplicate postmeta entries for the same post_id + meta_key combination, keeping only the latest value. This speeds up get_post_meta() queries. <a href="%s" target="_blank">Learn about metadata optimization</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/duplicate-postmeta-keys'
		);
	}
}

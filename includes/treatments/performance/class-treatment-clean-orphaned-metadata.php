<?php
/**
 * Treatment: Clean Orphaned Metadata
 *
 * Removes orphaned metadata rows from postmeta, usermeta, termmeta.
 *
 * Philosophy: Ridiculously Good (#7) - Free database cleanup
 * KB Link: https://wpshadow.com/kb/orphaned-metadata
 * Training: https://wpshadow.com/training/orphaned-metadata
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
 * Clean Orphaned Metadata treatment
 */
class Treatment_Clean_Orphaned_Metadata extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = [] ): bool {
		global $wpdb;

		$deleted = [];

		// Delete orphaned postmeta
		$postmeta_deleted = $wpdb->query(
			"DELETE pm FROM {$wpdb->postmeta} pm 
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.ID IS NULL"
		);
		if ( $postmeta_deleted ) {
			$deleted['postmeta'] = $postmeta_deleted;
		}

		// Delete orphaned usermeta
		$usermeta_deleted = $wpdb->query(
			"DELETE um FROM {$wpdb->usermeta} um 
			LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID 
			WHERE u.ID IS NULL"
		);
		if ( $usermeta_deleted ) {
			$deleted['usermeta'] = $usermeta_deleted;
		}

		// Delete orphaned termmeta
		$termmeta_deleted = $wpdb->query(
			"DELETE tm FROM {$wpdb->termmeta} tm 
			LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id 
			WHERE t.term_id IS NULL"
		);
		if ( $termmeta_deleted ) {
			$deleted['termmeta'] = $termmeta_deleted;
		}

		// Create backup with deletion counts
		self::create_backup( [
			'deleted'   => $deleted,
			'timestamp' => time(),
		] );

		$total_deleted = array_sum( $deleted );

		// Track KPI
		if ( $total_deleted > 0 ) {
			KPI_Tracker::record_treatment_applied( __CLASS__, 2 );
		}

		return $total_deleted > 0;
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		// Cannot restore deleted orphaned metadata (it was orphaned anyway)
		// This is a safe operation with no undo needed
		return true;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Clean Orphaned Metadata', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Removes orphaned metadata rows that reference deleted posts, users, or terms. Safe to delete as these serve no purpose. <a href="%s" target="_blank">Learn about metadata cleanup</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/orphaned-metadata'
		);
	}
}

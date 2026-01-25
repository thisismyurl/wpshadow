<?php
/**
 * Treatment: Clean Expired Transients
 *
 * Removes expired transients from the database.
 *
 * Philosophy: Ridiculously Good (#7) - Free database optimization
 * KB Link: https://wpshadow.com/kb/expired-transients-bloat
 * Training: https://wpshadow.com/training/expired-transients-bloat
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
 * Clean Expired Transients treatment
 */
class Treatment_Clean_Expired_Transients extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = array() ): bool {
		global $wpdb;

		// Create backup with count
		$before_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		$backup = array(
			'before_count' => $before_count,
			'timestamp'    => time(),
		);
		self::create_backup( $backup );

		// Delete expired transient timeouts
		$deleted_timeouts = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		// Delete corresponding transient values
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uses wpdb table property, properly prepared with placeholders
		$deleted_values = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_name NOT IN (
					SELECT CONCAT('_transient_', SUBSTRING(option_name, 19)) 
					FROM (
						SELECT option_name FROM {$wpdb->options} 
						WHERE option_name LIKE %s
					) as timeouts
				)",
				$wpdb->esc_like( '_transient_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_' ) . '%'
			)
		);

		// Same for site transients on multisite
		if ( is_multisite() ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uses wpdb table property, properly prepared with placeholders
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->sitemeta} 
					WHERE meta_key LIKE %s 
					AND meta_value < %d",
					$wpdb->esc_like( '_site_transient_timeout_' ) . '%',
					time()
				)
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uses wpdb table property, properly prepared with placeholders
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->sitemeta} 
					WHERE meta_key LIKE %s 
					AND meta_key NOT IN (
						SELECT CONCAT('_site_transient_', SUBSTRING(meta_key, 24)) 
						FROM (
							SELECT meta_key FROM {$wpdb->sitemeta} 
							WHERE meta_key LIKE %s
						) as timeouts
					)",
					$wpdb->esc_like( '_site_transient_' ) . '%',
					$wpdb->esc_like( '_site_transient_timeout_' ) . '%'
				)
			);
		}

		$total_deleted = $deleted_timeouts + $deleted_values;

		// Track KPI
		if ( $total_deleted > 0 ) {
			KPI_Tracker::record_treatment_applied( __CLASS__, 1 );
		}

		return $total_deleted > 0;
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		// Cannot restore deleted transients (they were expired anyway)
		// This is a safe operation with no undo needed
		return true;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Clean Expired Transients', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Removes expired transients from the database. These are cached values that have expired but weren\'t automatically cleaned up. Safe to delete. <a href="%s" target="_blank">Learn about transient cleanup</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/expired-transients-bloat'
		);
	}
}

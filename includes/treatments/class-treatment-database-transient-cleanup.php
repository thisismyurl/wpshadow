<?php
/**
 * Treatment for Database Transient Cleanup
 *
 * Cleans up expired transients from the database.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Database_Transient_Cleanup Class
 *
 * Removes expired transients from options table.
 *
 * @since 1.2601.2200
 */
class Treatment_Database_Transient_Cleanup extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since  1.2601.2200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'database-transient-expiration-cleanup';
	}

	/**
	 * Apply the treatment.
	 *
	 * Removes all expired transients from the database.
	 *
	 * @since  1.2601.2200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $data    Additional data about the operation.
	 * }
	 */
	public static function apply() {
		global $wpdb;

		// Get count before cleanup
		$before_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		// Delete expired transients
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		// Also delete the transient values
		$deleted_values = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_name NOT IN (
					SELECT CONCAT('_transient_', SUBSTRING(option_name, 19))
					FROM (
						SELECT option_name 
						FROM {$wpdb->options} 
						WHERE option_name LIKE %s
					) AS timeouts
				)",
				$wpdb->esc_like( '_transient_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_' ) . '%'
			)
		);

		// Site transients (multisite)
		if ( is_multisite() ) {
			$deleted_site = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->sitemeta} 
					WHERE meta_key LIKE %s 
					AND meta_value < %d",
					$wpdb->esc_like( '_site_transient_timeout_' ) . '%',
					time()
				)
			);

			$deleted_site_values = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->sitemeta} 
					WHERE meta_key LIKE %s 
					AND meta_key NOT IN (
						SELECT CONCAT('_site_transient_', SUBSTRING(meta_key, 24))
						FROM (
							SELECT meta_key 
							FROM {$wpdb->sitemeta} 
							WHERE meta_key LIKE %s
						) AS timeouts
					)",
					$wpdb->esc_like( '_site_transient_' ) . '%',
					$wpdb->esc_like( '_site_transient_timeout_' ) . '%'
				)
			);

			$deleted += $deleted_site + $deleted_site_values;
		}

		$total_deleted = $deleted + $deleted_values;

		if ( $total_deleted > 0 ) {
			// Log the cleanup
			\WPShadow\Core\Activity_Logger::log(
				'database_transient_cleanup',
				array(
					'deleted_count' => $total_deleted,
					'before_count'  => $before_count,
				)
			);

			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %d: number of transients cleaned */
					__( 'Cleaned up %d expired transients from database', 'wpshadow' ),
					$total_deleted
				),
				'data'    => array(
					'deleted_count' => $total_deleted,
					'before_count'  => $before_count,
				),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'No expired transients found to clean up', 'wpshadow' ),
			'data'    => array(
				'deleted_count' => 0,
			),
		);
	}
}

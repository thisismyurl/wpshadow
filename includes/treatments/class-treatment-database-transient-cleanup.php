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
		$deleted_count = 0;
		$all_options   = wp_load_alloptions();

		// Delete expired transients using WordPress API
		foreach ( $all_options as $option_name => $option_value ) {
			// Check for expired transient timeouts
			if ( strpos( $option_name, '_transient_timeout_' ) === 0 ) {
				$timeout = (int) $option_value;
				if ( $timeout < time() ) {
					// Get the transient name (remove the _transient_timeout_ prefix)
					$transient_name = substr( $option_name, 18 ); // strlen('_transient_timeout_') = 18

					// Delete both the timeout and the actual transient value
					delete_transient( $transient_name );
					$deleted_count++;
				}
			}
		}

		// Handle multisite
		if ( is_multisite() ) {
			$site_metas = get_site_option( 'site_meta', array() );

			// Get all site option names
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Need to scan all site meta for expired transients
			$sitemeta_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_id, meta_key, meta_value FROM {$wpdb->sitemeta}
					WHERE meta_key LIKE %s",
					'_site_transient_timeout_%'
				)
			);

			foreach ( $sitemeta_rows as $row ) {
				$timeout = (int) $row->meta_value;
				if ( $timeout < time() ) {
					$transient_name = substr( $row->meta_key, 23 ); // strlen('_site_transient_timeout_') = 23
					delete_site_transient( $transient_name );
					$deleted_count++;
				}
			}
		}

		if ( $deleted_count > 0 ) {
			// Log the cleanup
			\WPShadow\Core\Activity_Logger::log(
				'database_transient_cleanup',
				array(
					'deleted_count' => $deleted_count,
				)
			);

			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %d: number of transients cleaned */
					__( 'Cleaned up %d expired transients from database', 'wpshadow' ),
					$deleted_count
				),
				'data'    => array(
					'deleted_count' => $deleted_count,
				),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'No expired transients found', 'wpshadow' ),
			'data'    => array(
				'deleted_count' => 0,
			),
		);
	}
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

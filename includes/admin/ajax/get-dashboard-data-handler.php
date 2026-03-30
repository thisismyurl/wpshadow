<?php

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Options_Manager;

/**
 * AJAX Handler: Get updated dashboard data
 *
 * Action: wp_ajax_wpshadow_get_dashboard_data
 * Nonce: wpshadow_dashboard_nonce
 * Capability: read
 *
 * Returns: Updated gauges, findings, kanban data for real-time refresh
 *
 * @package WPShadow
 */
class Get_Dashboard_Data_Handler extends AJAX_Handler_Base {


	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_dashboard_data', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle dashboard data request
	 */
	public static function handle(): void {
		try {
			// Verify security
			self::verify_request( 'wpshadow_dashboard_nonce', 'manage_options' );

			$category_meta = \wpshadow_get_category_metadata();
			$last_scan     = (int) get_option( 'wpshadow_last_quick_scan', 0 );
			$never_run     = empty( $last_scan );

			$findings = \wpshadow_get_cached_findings();
			if ( empty( $findings ) ) {
				$findings = \wpshadow_get_site_findings();
			}

			$dismissed = Options_Manager::get_array( 'wpshadow_dismissed_findings', array() );
			$findings  = array_filter(
				$findings,
				function ( $f ) use ( $dismissed ) {
					return ! isset( $f['id'] ) || ! isset( $dismissed[ $f['id'] ] );
				}
			);

			$snapshot = \wpshadow_build_gauge_snapshot( array_values( $findings ), $category_meta );
			if ( function_exists( 'wpshadow_get_gauge_test_counts' ) ) {
				$snapshot['test_counts'] = \wpshadow_get_gauge_test_counts( $category_meta, $never_run );
			}

			self::send_success( $snapshot );
		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Legitimate error logging
			error_log( 'Dashboard Data Error: ' . $e->getMessage() );
			self::send_error( array( 'message' => __( 'Failed to retrieve dashboard data', 'wpshadow' ) ) );
		}
	}
}

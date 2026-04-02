<?php
/**
 * AJAX Handler: Run Privacy Report
 *
 * Generates and saves a privacy report for a user via AJAX.
 *
 * @package WPShadow
 * @subpackage AJAX
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Privacy\Consent_Preferences;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run_Privacy_Report_Handler Class
 *
 * @since 1.6093.1200
 */
class Run_Privacy_Report_Handler extends AJAX_Handler_Base {

	/**
	 * Register the AJAX hook.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_run_privacy_report', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle run privacy report request.
	 *
	 * Generates report data and saves a snapshot.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_refresh_privacy_reports', 'list_users', 'nonce' );

		$user_id = (int) self::get_post_param( 'user_id', 'int', 0, true );

		// Verify user can generate this report
		$current_user_id = get_current_user_id();
		$can_view_others = current_user_can( 'list_users' );

		if ( ! $can_view_others && $user_id !== $current_user_id ) {
			self::send_error( __( 'Insufficient permissions', 'wpshadow' ) );
		}

		if ( ! class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
			self::send_error( __( 'Report system not available', 'wpshadow' ) );
		}

		$selected_user = get_user_by( 'id', $user_id );
		if ( ! $selected_user ) {
			self::send_error( __( 'User not found', 'wpshadow' ) );
		}

		// Gather report data (same logic as user-privacy-report.php)
		$settings = get_option( 'wpshadow_settings', array() );
		$user_meta = get_user_meta( $user_id );
		
		$wpshadow_meta = array_filter(
			$user_meta,
			function ( $key ) {
				return 0 === strpos( $key, 'wpshadow_' );
			},
			ARRAY_FILTER_USE_KEY
		);

		$activity_logs      = array();
		$activity_log_count = 0;
		if ( class_exists( 'WPShadow\\Core\\Activity_Logger' ) ) {
			$result             = Activity_Logger::get_activities( array( 'user_id' => $user_id ), 500, 0 );
			$activity_logs      = isset( $result['activities'] ) ? $result['activities'] : array();
			$activity_log_count = isset( $result['total'] ) ? (int) $result['total'] : count( $activity_logs );
		}

		$consent = Consent_Preferences::get_preferences( $user_id );

		$findings = function_exists( 'wpshadow_get_cached_findings' )
			? wpshadow_get_cached_findings()
			: get_option( 'wpshadow_site_findings', array() );
		if ( ! is_array( $findings ) ) {
			$findings = array();
		}

		$selected_user_label = sprintf( '%1$s (%2$s)', $selected_user->display_name, $selected_user->user_email );

		$report_summary = array(
			'user_label'         => $selected_user_label,
			'settings_count'     => count( $settings ),
			'user_meta_count'    => count( $wpshadow_meta ),
			'activity_log_count' => $activity_log_count,
		);

		$report_data = array(
			'generated_at'  => current_time( 'mysql' ),
			'summary'       => $report_summary,
			'consent'       => $consent,
			'settings'      => $settings,
			'user_meta'     => $wpshadow_meta,
			'activity_logs' => $activity_logs,
			'findings'      => $findings,
		);

		// Save the snapshot
		try {
			Report_Snapshot_Manager::save_snapshot(
				'user-privacy-report',
				$report_data,
				array(
					'user_id'      => $user_id,
					'user_email'   => $selected_user->user_email,
					'requested_by' => $current_user_id,
					'summary'      => $report_summary,
				)
			);

			self::send_success( array(
				'message' => __( 'Privacy report generated successfully', 'wpshadow' ),
			) );
		} catch ( \Exception $e ) {
			self::send_error( __( 'Failed to save report snapshot', 'wpshadow' ) );
		}
	}
}

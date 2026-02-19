<?php
/**
 * AJAX Handler: Refresh Privacy Reports List
 *
 * Returns updated past reports list for the user privacy report page.
 * Used to refresh the past reports sidebar after a new report is generated.
 *
 * @package WPShadow
 * @subpackage AJAX
 * @since 1.6041.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Refresh_Privacy_Reports_Handler Class
 *
 * @since 1.6041.1200
 */
class Refresh_Privacy_Reports_Handler extends AJAX_Handler_Base {

	/**
	 * Register the AJAX hook.
	 *
	 * @since 1.6041.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_refresh_privacy_reports', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle refresh privacy reports request.
	 *
	 * Returns HTML for the past reports card to be inserted into the DOM.
	 *
	 * @since 1.6041.1200
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_refresh_privacy_reports', 'list_users', 'nonce' );

		$user_id = (int) self::get_post_param( 'user_id', 'int', 0, true );

		// Verify user can view this report
		$current_user_id = get_current_user_id();
		$can_view_others = current_user_can( 'list_users' );

		if ( ! $can_view_others && $user_id !== $current_user_id ) {
			self::send_error( __( 'Insufficient permissions', 'wpshadow' ) );
		}

		if ( ! class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
			self::send_error( __( 'Report system not available', 'wpshadow' ) );
		}

		if ( ! Report_Snapshot_Manager::has_snapshots_table() ) {
			self::send_error( __( 'Report database not available', 'wpshadow' ) );
		}

		// Get past reports
		$per_page = 10;
		$offset = 0;

		$past_reports_total = Report_Snapshot_Manager::get_snapshots_for_user_count( 'user-privacy-report', $user_id );
		$past_reports_pages = max( 1, (int) ceil( $past_reports_total / $per_page ) );
		$past_reports = Report_Snapshot_Manager::get_snapshots_for_user( 'user-privacy-report', $user_id, $per_page, $offset );

		// Build items array
		$past_reports_items = array();
		foreach ( $past_reports as $report ) {
			$past_reports_items[] = array(
				'title'   => __( 'Privacy Report', 'wpshadow' ),
				'time'    => $report['created_at'] ?? 0,
				'actions' => array(
					array(
						'label' => __( 'Download JSON', 'wpshadow' ),
						'url'   => wp_nonce_url(
							add_query_arg(
								array(
									'page'        => 'wpshadow-reports',
									'report'      => 'user-privacy-report',
									'user_id'     => $user_id,
									'download'    => 'json',
									'snapshot_id' => $report['id'],
								),
								admin_url( 'admin.php' )
							),
							'wpshadow_download_user_privacy_report',
							'nonce'
						),
					),
					array(
						'label' => __( 'Download PDF', 'wpshadow' ),
						'url'   => wp_nonce_url(
							add_query_arg(
								array(
									'page'        => 'wpshadow-reports',
									'report'      => 'user-privacy-report',
									'user_id'     => $user_id,
									'download'    => 'pdf',
									'snapshot_id' => $report['id'],
								),
								admin_url( 'admin.php' )
							),
							'wpshadow_download_user_privacy_report',
							'nonce'
						),
					),
				),
			);
		}

		// Render the card HTML
		ob_start();

		if ( function_exists( 'wpshadow_render_past_reports_card' ) ) {
			wpshadow_render_past_reports_card(
				array(
					'title'         => __( 'Past Reports', 'wpshadow' ),
					'description'   => __( 'Download previous privacy exports for reference or sharing.', 'wpshadow' ),
					'empty_message' => __( 'No past privacy reports yet.', 'wpshadow' ),
					'items'         => $past_reports_items,
					'pagination'    => array(
						'current'  => 1,
						'total'    => $past_reports_pages,
						'param'    => 'privacy_past_page',
						'base_url' => add_query_arg(
							array(
								'page'    => 'wpshadow-reports',
								'report'  => 'user-privacy-report',
								'user_id' => $user_id,
							),
							admin_url( 'admin.php' )
						),
					),
					'delete_action' => $can_view_others || $user_id === $current_user_id
						? array(
							'action_url'   => add_query_arg(
									array(
										'page'    => 'wpshadow-reports',
										'report'  => 'user-privacy-report',
										'user_id' => $user_id,
									),
									admin_url( 'admin.php' )
								),
							'nonce_action' => 'wpshadow_delete_privacy_reports',
							'nonce_name'   => 'wpshadow_delete_privacy_reports_nonce',
							'fields'       => array(
								'wpshadow_delete_privacy_reports' => 1,
							),
							'label'        => __( 'Delete All Reports', 'wpshadow' ),
							'confirm'      => __( 'Delete all past privacy reports? This cannot be undone.', 'wpshadow' ),
						)
						: array(),
				)
			);
		}

		$html = ob_get_clean();

		self::send_success( array( 'html' => $html ) );
	}
}

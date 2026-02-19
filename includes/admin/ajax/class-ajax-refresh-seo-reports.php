<?php
/**
 * AJAX Handler for Refreshing SEO Reports List
 *
 * Handles the AJAX request to refresh the past SEO reports list.
 *
 * @package WPShadow\Admin\Ajax
 * @since   1.6041.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Refresh SEO Reports AJAX Handler
 *
 * Returns updated HTML for the past SEO reports sidebar.
 *
 * @since 1.6041.1200
 */
class Refresh_SEO_Reports_Handler extends AJAX_Handler_Base {

	/**
	 * Register the AJAX handler.
	 *
	 * @since 1.6041.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_refresh_seo_reports', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request to refresh SEO reports list.
	 *
	 * @since 1.6041.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle(): void {
		// Verify nonce and capability.
		self::verify_request( 'wpshadow_refresh_seo_reports', 'manage_options', 'nonce' );

		// Check if Report_Snapshot_Manager class exists.
		if ( ! class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
			self::send_error( __( 'Report system not available', 'wpshadow' ) );
		}

		if ( ! Report_Snapshot_Manager::has_snapshots_table() ) {
			self::send_error( __( 'Report table not found', 'wpshadow' ) );
		}

		// Get past reports (site-level, so no specific user).
		$past_reports_per_page = 10;
		$past_reports_total    = Report_Snapshot_Manager::get_snapshots_count( 'seo-report' );
		$past_reports_pages    = max( 1, (int) ceil( $past_reports_total / $past_reports_per_page ) );
		$past_reports          = Report_Snapshot_Manager::get_snapshots_paginated( 'seo-report', $past_reports_per_page, 0 );

		// Build items array.
		$past_reports_items = array();
		foreach ( $past_reports as $report ) {
			$past_reports_items[] = array(
				'title'   => __( 'SEO Report', 'wpshadow' ),
				'time'    => $report['created_at'] ?? 0,
				'actions' => array(
					array(
						'label' => __( 'Download JSON', 'wpshadow' ),
						'url'   => wp_nonce_url(
							add_query_arg(
								array(
									'page'        => 'wpshadow-reports',
									'report'      => 'seo-report',
									'download'    => 'json',
									'snapshot_id' => $report['id'],
								),
								admin_url( 'admin.php' )
							),
							'wpshadow_download_seo_report',
							'nonce'
						),
					),
					array(
						'label' => __( 'Download PDF', 'wpshadow' ),
						'url'   => wp_nonce_url(
							add_query_arg(
								array(
									'page'        => 'wpshadow-reports',
									'report'      => 'seo-report',
									'download'    => 'pdf',
									'snapshot_id' => $report['id'],
								),
								admin_url( 'admin.php' )
							),
							'wpshadow_download_seo_report',
							'nonce'
						),
					),
				),
			);
		}

		// Load the past reports card helper if not already loaded.
		$past_reports_helper = WPSHADOW_PATH . 'includes/views/reports/partials/past-reports.php';
		if ( file_exists( $past_reports_helper ) ) {
			require_once $past_reports_helper;
		}

		// Render the past reports card.
		ob_start();
		if ( function_exists( 'wpshadow_render_past_reports_card' ) ) {
			wpshadow_render_past_reports_card(
				array(
					'title'         => __( 'Past Reports', 'wpshadow' ),
					'description'   => __( 'Download previous SEO reports for comparison.', 'wpshadow' ),
					'empty_message' => __( 'No past SEO reports yet.', 'wpshadow' ),
					'items'         => $past_reports_items,
					'pagination'    => array(
						'current'  => 1,
						'total'    => $past_reports_pages,
						'param'    => 'seo_past_page',
						'base_url' => add_query_arg(
							array(
								'page'   => 'wpshadow-reports',
								'report' => 'seo-report',
							),
							admin_url( 'admin.php' )
						),
					),
					'delete_action' => current_user_can( 'manage_options' ) && ! empty( $past_reports_items )
						? array(
							'action_url'   => add_query_arg(
								array(
									'page'   => 'wpshadow-reports',
									'report' => 'seo-report',
								),
								admin_url( 'admin.php' )
							),
							'nonce_action' => 'wpshadow_delete_seo_reports',
							'nonce_name'   => 'wpshadow_delete_seo_reports_nonce',
							'fields'       => array(
								'wpshadow_delete_seo_reports' => 1,
							),
							'label'        => __( 'Delete All Reports', 'wpshadow' ),
							'confirm'      => __( 'Delete all past SEO reports? This cannot be undone.', 'wpshadow' ),
						)
						: array(),
				)
			);
		}
		$html = ob_get_clean();

		self::send_success(
			array(
				'html' => $html,
			)
		);
	}
}

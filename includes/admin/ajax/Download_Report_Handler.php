<?php
/**
 * AJAX Handler: Download Report
 *
 * Action: wp_ajax_wpshadow_download_report
 * Nonce: wpshadow_download_report (URL param)
 * Capability: manage_options
 *
 * @package WPShadow\Admin\Ajax
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reports\Report_Engine;
use WPShadow\Reports\Report_Renderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Download Report AJAX Handler
 */
class Download_Report_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_download_report', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle download request
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	public static function handle(): void {
		// Verify nonce and capability (URL-based nonce)
		check_admin_referer( 'wpshadow_download_report', '_wpnonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		// Get parameters from GET (for download links)
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : date( 'Y-m-d', strtotime( '-30 days' ) );
		$date_to   = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : date( 'Y-m-d' );
		$category  = isset( $_GET['category'] ) ? sanitize_text_field( wp_unslash( $_GET['category'] ) ) : '';
		$type      = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'summary';
		$format    = isset( $_GET['format'] ) ? sanitize_text_field( wp_unslash( $_GET['format'] ) ) : 'csv';

		// Validate format
		if ( ! in_array( $format, array( 'html', 'csv', 'json' ), true ) ) {
			$format = 'csv';
		}

		try {
			// Generate report
			$report = Report_Engine::generate(
				array(
					'date_from' => $date_from,
					'date_to'   => $date_to,
					'category'  => $category,
					'type'      => $type,
				)
			);

			// Build filename
			$filename = sprintf(
				'wpshadow-report-%s-to-%s.%s',
				$date_from,
				$date_to,
				$format
			);

			// Download report
			Report_Renderer::download_report( $report, $format, $filename );

		} catch ( \Exception $e ) {
			wp_die(
				sprintf(
					esc_html__( 'Error downloading report: %s', 'wpshadow' ),
					esc_html( $e->getMessage() )
				)
			);
		}
	}
}

<?php
/**
 * AJAX Handler for PDF Report Export
 *
 * Handles PDF report generation and download
 *
 * @since 0.6093.1200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\PDF_Report_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Export_PDF_Report Class
 *
 * Handles PDF report export requests
 *
 * @since 0.6093.1200
 */
class AJAX_Export_PDF_Report extends AJAX_Handler_Base {

	/**
	 * Handle export request
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_export_report', 'manage_options' );

		// Get parameters
		$report_type = self::get_post_param( 'report_type', 'text', 'summary', true );
		$findings_json = self::get_post_param( 'findings', 'text', '[]' );

		try {
			// Parse findings
			$findings = json_decode( wp_unslash( $findings_json ), true );
			if ( ! is_array( $findings ) ) {
				$findings = array();
			}

			// Generate PDF report
			$pdf_file = PDF_Report_Generator::generate_pdf( $report_type, $findings );

			if ( ! $pdf_file ) {
				self::send_error( __( 'Failed to generate PDF report', 'wpshadow' ) );
				return;
			}

			// Get download URL
			$download_url = PDF_Report_Generator::get_report_url( $pdf_file );

			if ( ! $download_url ) {
				self::send_error( __( 'Failed to generate download URL', 'wpshadow' ) );
				return;
			}

			self::send_success( array(
				'message'        => __( 'PDF report generated successfully', 'wpshadow' ),
				'download_url'   => $download_url,
				'report_type'    => $report_type,
				'findings_count' => count( $findings ),
			) );

		} catch ( \Exception $e ) {
			self::send_error( sprintf(
				/* translators: %s: error message */
				__( 'Error generating report: %s', 'wpshadow' ),
				$e->getMessage()
			) );
		}
	}
}

// Register AJAX handler
add_action( 'wp_ajax_wpshadow_export_pdf_report', array(
	'WPShadow\\Admin\\AJAX\\AJAX_Export_PDF_Report',
	'handle',
) );

// Also register for non-authenticated users if reports are public
// Uncomment if needed: add_action( 'wp_ajax_nopriv_wpshadow_export_pdf_report', ... );

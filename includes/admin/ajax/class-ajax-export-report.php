<?php
/**
 * AJAX Handler: Export Report
 *
 * Handles report export requests (PDF, CSV, Excel).
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\Report_Export_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Export_Report Class
 *
 * @since 1.6093.1200
 */
class AJAX_Export_Report extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_export_report', 'manage_options' );
		
		$report_id = self::get_post_param( 'report_id', 'text', '', true );
		$format    = self::get_post_param( 'format', 'text', 'pdf', true );
		$data_json = self::get_post_param( 'data', 'text', '', true );
		
		$data = json_decode( stripslashes( $data_json ), true );
		
		if ( ! $data ) {
			self::send_error( __( 'Invalid report data', 'wpshadow' ) );
		}
		
		$filepath = '';
		
		switch ( $format ) {
			case 'pdf':
				$filepath = Report_Export_Manager::export_pdf( $report_id, $data );
				break;
			case 'csv':
				$filepath = Report_Export_Manager::export_csv( $report_id, $data );
				break;
			case 'excel':
				$filepath = Report_Export_Manager::export_csv( $report_id, $data ); // Same as CSV for now
				break;
			default:
				self::send_error( __( 'Invalid export format', 'wpshadow' ) );
		}
		
		if ( ! $filepath ) {
			self::send_error( __( 'Export failed', 'wpshadow' ) );
		}
		
		$download_url = Report_Export_Manager::get_download_url( $filepath );
		
		self::send_success( array(
			'message'      => __( 'Report exported successfully', 'wpshadow' ),
			'download_url' => $download_url,
			'filename'     => basename( $filepath ),
		) );
	}
}

add_action( 'wp_ajax_wpshadow_export_report', array( 'WPShadow\Admin\AJAX_Export_Report', 'handle' ) );

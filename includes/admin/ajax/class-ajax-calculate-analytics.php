<?php
/**
 * AJAX Handler: Calculate Analytics
 *
 * Handles advanced analytics calculations (ROI, benchmarks, what-if).
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.2603.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\Report_Analytics_Engine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Calculate_Analytics Class
 *
 * @since 1.2603.0200
 */
class AJAX_Calculate_Analytics extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request
	 *
	 * @since  1.2603.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_calculate_analytics', 'manage_options' );
		
		$calculation_type = self::get_post_param( 'type', 'text', '', true );
		$data_json        = self::get_post_param( 'data', 'text', '', true );
		
		$data = json_decode( stripslashes( $data_json ), true );
		
		if ( ! $data ) {
			self::send_error( __( 'Invalid data', 'wpshadow' ) );
		}
		
		$result = null;
		
		switch ( $calculation_type ) {
			case 'roi':
				$findings = isset( $data['findings'] ) ? $data['findings'] : array();
				$result = Report_Analytics_Engine::calculate_roi( $findings );
				break;
				
			case 'executive_summary':
				$findings = isset( $data['findings'] ) ? $data['findings'] : array();
				$result = Report_Analytics_Engine::generate_executive_summary( $findings );
				break;
				
			case 'regression':
				$report_id = isset( $data['report_id'] ) ? $data['report_id'] : '';
				$days = isset( $data['days'] ) ? absint( $data['days'] ) : 7;
				$result = Report_Analytics_Engine::detect_regressions( $report_id, $days );
				break;
				
			case 'what_if':
				$findings = isset( $data['findings'] ) ? $data['findings'] : array();
				$fixes = isset( $data['fixes'] ) ? $data['fixes'] : array();
				$result = Report_Analytics_Engine::simulate_fixes( $findings, $fixes );
				break;
				
			case 'benchmark':
				$findings = isset( $data['findings'] ) ? $data['findings'] : array();
				$site_type = isset( $data['site_type'] ) ? sanitize_key( $data['site_type'] ) : 'business';
				$result = Report_Analytics_Engine::compare_to_benchmarks( $findings, $site_type );
				break;
				
			default:
				self::send_error( __( 'Invalid calculation type', 'wpshadow' ) );
		}
		
		if ( ! $result ) {
			self::send_error( __( 'Calculation failed', 'wpshadow' ) );
		}
		
		self::send_success( array(
			'message' => __( 'Analytics calculated successfully', 'wpshadow' ),
			'result'  => $result,
		) );
	}
}

add_action( 'wp_ajax_wpshadow_calculate_analytics', array( 'WPShadow\Admin\AJAX_Calculate_Analytics', 'handle' ) );

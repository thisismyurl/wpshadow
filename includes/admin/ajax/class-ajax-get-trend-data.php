<?php
/**
 * AJAX Handler: Get Trend Data
 *
 * Handles retrieving trend analysis data.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Get_Trend_Data Class
 *
 * @since 1.6093.1200
 */
class AJAX_Get_Trend_Data extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_get_trend_data', 'manage_options' );
		
		$report_id = self::get_post_param( 'report_id', 'text', '', true );
		$days      = self::get_post_param( 'days', 'int', 30 );
		
		$trend_data = Report_Snapshot_Manager::get_trend_data( $report_id, $days );
		
		if ( ! $trend_data ) {
			self::send_error( __( 'Failed to retrieve trend data', 'wpshadow' ) );
		}
		
		self::send_success( array(
			'message'    => __( 'Trend data retrieved successfully', 'wpshadow' ),
			'trend_data' => $trend_data,
		) );
	}
}

add_action( 'wp_ajax_wpshadow_get_trend_data', array( 'WPShadow\Admin\AJAX_Get_Trend_Data', 'handle' ) );

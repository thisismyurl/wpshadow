<?php
/**
 * AJAX Handler: Save Snapshot
 *
 * Handles saving report snapshots for historical comparison.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.603.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Save_Snapshot Class
 *
 * @since 1.603.0200
 */
class AJAX_Save_Snapshot extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request
	 *
	 * @since  1.603.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_save_snapshot', 'manage_options' );
		
		$report_id = self::get_post_param( 'report_id', 'text', '', true );
		$data_json = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
		$metadata_json = isset( $_POST['metadata'] ) ? wp_unslash( $_POST['metadata'] ) : '{}';
		
		$data = json_decode( $data_json, true );
		$data_error = json_last_error();

		if ( empty( $data ) || JSON_ERROR_NONE !== $data_error ) {
			self::send_error( __( 'Invalid snapshot data', 'wpshadow' ) );
		}
		
		$metadata = json_decode( $metadata_json, true );
		$metadata_error = json_last_error();
		if ( JSON_ERROR_NONE !== $metadata_error ) {
			$metadata = array();
		}
		
		$snapshot_id = Report_Snapshot_Manager::save_snapshot( $report_id, $data, $metadata );
		
		if ( ! $snapshot_id ) {
			self::send_error( __( 'Failed to save snapshot', 'wpshadow' ) );
		}
		
		$snapshots = Report_Snapshot_Manager::get_snapshots( $report_id, 1 );
		$snapshot  = ! empty( $snapshots ) ? $snapshots[0] : null;
		
		self::send_success( array(
			'message'     => __( 'Snapshot saved successfully', 'wpshadow' ),
			'snapshot_id' => $snapshot_id,
			'snapshot'    => $snapshot,
		) );
	}
}

add_action( 'wp_ajax_wpshadow_save_snapshot', array( 'WPShadow\Admin\AJAX_Save_Snapshot', 'handle' ) );

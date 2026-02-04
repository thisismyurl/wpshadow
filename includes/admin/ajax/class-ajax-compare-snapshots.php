<?php
/**
 * AJAX Handler: Compare Snapshots
 *
 * Handles comparing two report snapshots.
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
 * AJAX_Compare_Snapshots Class
 *
 * @since 1.603.0200
 */
class AJAX_Compare_Snapshots extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request
	 *
	 * @since  1.603.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_compare_snapshots', 'manage_options' );
		
		$snapshot_id_1 = self::get_post_param( 'snapshot_id_1', 'int', 0, true );
		$snapshot_id_2 = self::get_post_param( 'snapshot_id_2', 'int', 0, true );
		
		if ( ! $snapshot_id_1 || ! $snapshot_id_2 ) {
			self::send_error( __( 'Invalid snapshot IDs', 'wpshadow' ) );
		}
		
		$comparison = Report_Snapshot_Manager::compare_snapshots( $snapshot_id_1, $snapshot_id_2 );
		
		if ( ! $comparison ) {
			self::send_error( __( 'Failed to compare snapshots', 'wpshadow' ) );
		}
		
		self::send_success( array(
			'message'    => __( 'Snapshots compared successfully', 'wpshadow' ),
			'comparison' => $comparison,
		) );
	}
}

add_action( 'wp_ajax_wpshadow_compare_snapshots', array( 'WPShadow\Admin\AJAX_Compare_Snapshots', 'handle' ) );

<?php
/**
 * AJAX Handler: Add Annotation
 *
 * Handles adding notes/annotations to report findings.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.603.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\Report_Annotation_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Add_Annotation Class
 *
 * @since 1.603.0200
 */
class AJAX_Add_Annotation extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request
	 *
	 * @since  1.603.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_add_annotation', 'manage_options' );
		
		$report_id  = self::get_post_param( 'report_id', 'text', '', true );
		$finding_id = self::get_post_param( 'finding_id', 'text', '', true );
		$text       = self::get_post_param( 'text', 'textarea', '', true );
		$action     = self::get_post_param( 'action_taken', 'text', '' );
		$status     = self::get_post_param( 'status', 'text', 'open' );
		
		$options = array();
		if ( $action ) {
			$options['action_taken'] = $action;
		}
		if ( $status ) {
			$options['status'] = $status;
		}
		
		$annotation_id = Report_Annotation_Manager::add_annotation( $report_id, $finding_id, $text, $options );
		
		if ( ! $annotation_id ) {
			self::send_error( __( 'Failed to add annotation', 'wpshadow' ) );
		}
		
		self::send_success( array(
			'message'       => __( 'Annotation added successfully', 'wpshadow' ),
			'annotation_id' => $annotation_id,
		) );
	}
}

add_action( 'wp_ajax_wpshadow_add_annotation', array( 'WPShadow\Admin\AJAX_Add_Annotation', 'handle' ) );

<?php
/**
 * AJAX Handler: Send Integration
 *
 * Handles sending reports to external services (Slack, Teams, webhooks).
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Reporting\Report_Integration_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Send_Integration Class
 *
 * @since 0.6093.1200
 */
class AJAX_Send_Integration extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_send_integration', 'manage_options' );

		$service   = self::get_post_param( 'service', 'text', '', true );
		$report_id = self::get_post_param( 'report_id', 'text', '', true );
		$url       = self::get_post_param( 'url', 'url', '', true );
		$data_json = self::get_post_param( 'data', 'text', '', true );

		$data = json_decode( stripslashes( $data_json ), true );

		if ( ! $data ) {
			self::send_error( __( 'Invalid report data', 'wpshadow' ) );
		}

		$result = false;

		switch ( $service ) {
			case 'slack':
				$result = Report_Integration_Manager::send_to_slack( $url, $report_id, $data );
				break;
			case 'teams':
				$result = Report_Integration_Manager::send_to_teams( $url, $report_id, $data );
				break;
			case 'webhook':
				$method = self::get_post_param( 'method', 'text', 'POST' );
				$result = Report_Integration_Manager::trigger_webhook( $url, $report_id, $data, $method );
				break;
			default:
				self::send_error( __( 'Invalid service', 'wpshadow' ) );
		}

		if ( is_wp_error( $result ) ) {
			self::send_error( $result->get_error_message() );
		}

		if ( ! $result ) {
			self::send_error( __( 'Integration failed', 'wpshadow' ) );
		}

		self::send_success( array(
			'message' => sprintf(
				/* translators: %s: service name */
				__( 'Report sent to %s successfully', 'wpshadow' ),
				ucfirst( $service )
			),
		) );
	}
}

add_action( 'wp_ajax_wpshadow_send_integration', array( 'WPShadow\Admin\AJAX_Send_Integration', 'handle' ) );

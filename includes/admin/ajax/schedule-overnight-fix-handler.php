<?php

/**
 * Schedule Overnight Fix AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Options_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Schedule_Overnight_Fix_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hooks for overnight fix scheduling.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_schedule_overnight_fix', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle overnight fix scheduling requests.
	 *
	 * @since 0.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_kanban', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'key', '', true );

		$scheduled   = Options_Manager::get_array( 'wpshadow_scheduled_fixes', array() );
		$scheduled[] = array(
			'finding_id'   => $finding_id,
			'scheduled_at' => current_time( 'timestamp' ),
			'user_email'   => wp_get_current_user()->user_email,
		);
		update_option( 'wpshadow_scheduled_fixes', $scheduled );

		if ( ! wp_next_scheduled( 'wpshadow_run_overnight_fixes' ) ) {
			$tomorrow_2am = strtotime( 'tomorrow 2:00' );
			wp_schedule_single_event( $tomorrow_2am, 'wpshadow_run_overnight_fixes' );
		}

		self::send_success(
			array(
				'message'    => __( 'Fix scheduled for overnight processing.', 'wpshadow' ),
				'finding_id' => $finding_id,
			)
		);
	}
}

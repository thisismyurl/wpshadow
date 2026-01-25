<?php

/**
 * Dismiss Finding AJAX Handler
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

class Dismiss_Finding_Handler extends AJAX_Handler_Base {

	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dismiss_finding', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		self::verify_request( 'wpshadow_dismiss_finding', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '', true );
		if ( empty( $finding_id ) ) {
			self::send_error( __( 'Invalid finding ID', 'wpshadow' ) );
		}

		$dismissed                = Options_Manager::get_array( 'wpshadow_dismissed_findings', array() );
		$dismissed[ $finding_id ] = current_time( 'timestamp' );
		update_option( 'wpshadow_dismissed_findings', $dismissed );

		// Log activity (Issue #565)
		\WPShadow\Core\Activity_Logger::log( 'finding_dismissed', "Finding dismissed: {$finding_id}", '', array( 'finding_id' => $finding_id ) );

		self::send_success( array( 'message' => __( 'Finding dismissed', 'wpshadow' ) ) );
	}
}

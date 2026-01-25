<?php
/**
 * Rollback Treatment AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handler for rolling back treatments
 */
class Rollback_Treatment_Handler extends AJAX_Handler_Base {
	/**
	 * Register the AJAX handler
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_rollback_treatment', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_rollback', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '' );

		if ( empty( $finding_id ) ) {
			self::send_error( __( 'Finding ID is required.', 'wpshadow' ) );
			return;
		}

		// Check if treatment can be rolled back
		if ( ! \wpshadow_can_rollback( $finding_id ) ) {
			self::send_error( __( 'This treatment does not support rollback.', 'wpshadow' ) );
			return;
		}

		// Rollback the treatment
		$result = \wpshadow_rollback_fix( $finding_id );

		if ( is_array( $result ) && ! empty( $result['success'] ) ) {
			// Log the rollback
			\WPShadow\Core\Activity_Logger::log(
				'treatment_rolled_back',
				sprintf( 'Treatment rolled back: %s', $finding_id ),
				'treatments',
				array(
					'finding_id' => $finding_id,
					'message'    => $result['message'] ?? '',
				)
			);
			self::send_success( $result );
		} else {
			self::send_error(
				$result['message'] ?? __( 'Rollback failed.', 'wpshadow' ),
				$result
			);
		}
	}
}

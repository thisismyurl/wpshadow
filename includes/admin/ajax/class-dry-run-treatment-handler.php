<?php
/**
 * Dry Run Treatment AJAX Handler
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
 * Handler for dry-run treatment execution
 */
class Dry_Run_Treatment_Handler extends AJAX_Handler_Base {
	/**
	 * Register the AJAX handler
	 */
	public static function register() : void {
		add_action( 'wp_ajax_wpshadow_dry_run_treatment', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle the AJAX request
	 */
	public static function handle() : void {
		self::verify_request( 'wpshadow_dry_run', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '' );
		
		if ( empty( $finding_id ) ) {
			self::send_error( __( 'Finding ID is required.', 'wpshadow' ) );
			return;
		}
		
		// Run treatment in dry-run mode
		$result = \wpshadow_attempt_autofix( $finding_id, true );

		if ( is_array( $result ) && ! empty( $result['success'] ) ) {
			// Log the dry run
			\WPShadow\Core\Activity_Logger::log(
				'treatment_dry_run',
				sprintf( 'Dry run completed: %s', $finding_id ),
				'treatments',
				array(
					'finding_id'  => $finding_id,
					'would_apply' => $result['would_apply'] ?? false,
					'message'     => $result['message'] ?? '',
				)
			);
			self::send_success( $result );
		} else {
			self::send_error(
				$result['message'] ?? __( 'Dry run failed.', 'wpshadow' ),
				$result
			);
		}
	}
}

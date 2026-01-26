<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX Handler: Dismiss Scan Notice
 *
 * Action: wp_ajax_wpshadow_dismiss_scan_notice
 * Nonce: wpshadow_scan_notice_nonce
 * Capability: manage_options
 *
 * Philosophy: Helpful neighbor (#1) - Don't nag, but remind gently
 *
 * @package WPShadow
 */
class Dismiss_Scan_Notice_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dismiss_scan_notice', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle dismiss scan notice AJAX request
	 */
	public static function handle(): void {
		try {
			// Verify security
			self::verify_request( 'wpshadow_scan_notice_nonce', 'manage_options' );

			// Store dismiss timestamp (1 hour from now)
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'wpshadow_scan_notice_dismissed_until', time() + HOUR_IN_SECONDS );

			self::send_success(
				array(
					'message' => __( 'Scan reminder dismissed for 1 hour.', 'wpshadow' ),
				)
			);

		} catch ( \Exception $e ) {
			self::send_error( $e->getMessage() );
		}
	}
}

<?php
/**
 * AJAX Handler: Dismiss 404 Monitor Pro Notice
 *
 * Allows users to dismiss the pro features notice on the 404 Monitor utility page.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * Dismiss 404 Monitor Pro Notice Handler
 *
 * @since 1.6093.1200
 */
class Dismiss_404_Monitor_Notice_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dismiss_notice', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle dismiss notice AJAX request
	 *
	 * Handles dismissal of various dismissible notices across WPShadow tools.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle(): void {
		// Verify nonce and capability.
		self::verify_request( 'wpshadow_dashboard_nonce', 'manage_options', 'nonce' );

		// Get the notice ID to dismiss.
		$notice_id = self::get_post_param( 'notice_id', 'text', '', true );

		// Mark notice as dismissed for this user.
		update_user_meta( get_current_user_id(), 'wpshadow_' . $notice_id . '_dismissed', true );

		self::send_success(
			array(
				'message' => __( 'Notice dismissed', 'wpshadow' ),
			)
		);
	}
}

// Register the handler.
Dismiss_404_Monitor_Notice_Handler::register();

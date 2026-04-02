<?php
/**
 * AJAX Handler: Dismiss Vault Offsite Storage Notice
 *
 * Allows users to dismiss the free offsite storage promotional notice
 * on the Vault Light utilities page.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * Dismiss Vault Offsite Storage Notice Handler
 *
 * @since 1.6093.1200
 */
class Dismiss_Vault_Offsite_Notice_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dismiss_vault_offsite_notice', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle dismiss vault offsite storage notice AJAX request
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle(): void {
		// Verify nonce and capability.
		self::verify_request( 'wpshadow_dismiss_vault_offsite_notice', 'manage_options', 'nonce' );

		// Mark notice as dismissed for this user.
		update_user_meta( get_current_user_id(), 'wpshadow_vault_offsite_notice_dismissed', true );

		self::send_success(
			array(
				'message' => __( 'Notice dismissed', 'wpshadow' ),
			)
		);
	}
}

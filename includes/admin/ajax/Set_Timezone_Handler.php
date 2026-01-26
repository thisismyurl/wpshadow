<?php
/**
 * Set Timezone AJAX Handler
 *
 * Handles AJAX requests to manually set timezone.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Utils\Timezone_Manager;

/**
 * AJAX handler for manually setting timezone
 *
 * Action: wp_ajax_wpshadow_set_timezone
 * Nonce: wpshadow_timezone_nonce
 * Capability: manage_options
 */
class Set_Timezone_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_set_timezone', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to set timezone
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_timezone_nonce', 'manage_options' );

		$timezone = self::get_post_param( 'timezone', '' );

		if ( ! $timezone || ! Timezone_Manager::is_valid_timezone( $timezone ) ) {
			self::send_error( __( 'Invalid timezone', 'wpshadow' ) );
		}

		Timezone_Manager::set_admin_timezone( $timezone );

		self::send_success(
			array(
				'timezone' => $timezone,
				'message'  => sprintf( __( 'Timezone set to: %s', 'wpshadow' ), $timezone ),
			)
		);
	}
}

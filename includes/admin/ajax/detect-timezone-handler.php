<?php
/**
 * Detect Timezone AJAX Handler
 *
 * Handles AJAX requests to detect user timezone from browser.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Timezone_Manager;

/**
 * AJAX handler for detecting timezone from browser
 *
 * Action: wp_ajax_wpshadow_detect_timezone
 * Nonce: wpshadow_timezone_nonce
 * Capability: manage_options
 */
class Detect_Timezone_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_detect_timezone', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to detect timezone
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_timezone_nonce', 'manage_options' );

		// Client sends timezone as query parameter (detected via JS Intl API)
		$timezone = self::get_post_param( 'timezone', '' );

		if ( ! $timezone || ! Timezone_Manager::is_valid_timezone( $timezone ) ) {
			self::send_error( __( 'Invalid timezone', 'wpshadow' ) );
		}

		// Store detected timezone
		Timezone_Manager::set_admin_timezone( $timezone );

		self::send_success(
			array(
				'timezone' => $timezone,
				'message'  => sprintf( __( 'Timezone detected: %s', 'wpshadow' ), $timezone ),
			)
		);
	}
}

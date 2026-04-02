<?php
/**
 * Generate Password AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Generate_Password_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hooks for password generation.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_generate_password', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle password generation requests.
	 *
	 * @since 1.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_generate_password', 'create_users', 'nonce' );

		$password = \wpshadow_generate_friendly_password();
		self::send_success( array( 'password' => $password ) );
	}
}

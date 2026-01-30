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
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_generate_password', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		self::verify_request( 'wpshadow_generate_password', 'create_users', 'nonce' );

		$password = \wpshadow_generate_friendly_password();
		self::send_success( array( 'password' => $password ) );
	}
}

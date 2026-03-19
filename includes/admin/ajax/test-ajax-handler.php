<?php
/**
 * Test AJAX Handler
 *
 * Simple test to verify AJAX is working
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_AJAX_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hooks for connectivity tests.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_test_ajax', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX connectivity test requests.
	 *
	 * @since 1.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_test_ajax', 'read' );
		wp_send_json_success( array( 'message' => 'AJAX is working!' ) );
	}
}

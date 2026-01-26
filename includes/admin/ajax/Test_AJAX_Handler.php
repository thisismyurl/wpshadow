<?php
/**
 * Test AJAX Handler
 *
 * Simple test to verify AJAX is working
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_AJAX_Handler {
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_test_ajax', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		wp_send_json_success( array( 'message' => 'AJAX is working!' ) );
	}
}

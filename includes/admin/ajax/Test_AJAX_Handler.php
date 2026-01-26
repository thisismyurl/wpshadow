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
		// Log that register was called
		$debug_file = defined( 'WPSHADOW_PATH' ) ? WPSHADOW_PATH . 'debug-ajax.log' : '/tmp/debug-ajax.log';
		file_put_contents( $debug_file, "Test_AJAX_Handler::register() called at " . date( 'Y-m-d H:i:s' ) . " - Adding action wp_ajax_wpshadow_test_ajax\n", FILE_APPEND );
		
		add_action( 'wp_ajax_wpshadow_test_ajax', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		$debug_file = defined( 'WPSHADOW_PATH' ) ? WPSHADOW_PATH . 'debug-ajax.log' : '/tmp/debug-ajax.log';
		file_put_contents( $debug_file, "Test_AJAX_Handler::handle() called at " . date( 'Y-m-d H:i:s' ) . "\n", FILE_APPEND );
		wp_send_json_success( array( 'message' => 'AJAX is working!' ) );
	}
}


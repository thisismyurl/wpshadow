<?php
/**
 * Clear Cache AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Clear_Cache_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hooks for cache-clearing requests.
	 *
	 * @since  1.6047.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_clear_cache', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle cache-clearing requests.
	 *
	 * @since 1.6047.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_cache_nonce', 'manage_options', 'nonce' );

		$cache_dir = WP_CONTENT_DIR . '/cache/wpshadow';
		if ( is_dir( $cache_dir ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			\WP_Filesystem();
			global $wp_filesystem;
			if ( $wp_filesystem && $wp_filesystem->is_dir( $cache_dir ) ) {
				$wp_filesystem->delete( $cache_dir, true );
			}
		}

		wp_cache_flush();

		Activity_Logger::log(
			'cache_cleared',
			__( 'Cache cleared successfully.', 'wpshadow' ),
			'performance'
		);

		self::send_success( array( 'message' => __( 'Cache cleared successfully.', 'wpshadow' ) ) );
	}
}

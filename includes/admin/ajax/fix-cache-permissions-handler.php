<?php
/**
 * Fix Cache Permissions AJAX Handler
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

/**
 * Fix Cache Permissions Handler
 *
 * Attempts to create and set proper permissions on the cache directory.
 *
 * @since 1.6093.1200
 */
class Fix_Cache_Permissions_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX action
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_fix_cache_permissions', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_fix_permissions', 'manage_options', 'nonce' );

		$cache_dir = WP_CONTENT_DIR . '/cache/wpshadow';
		$parent_dir = WP_CONTENT_DIR . '/cache';

		// Try to create directories with proper permissions.
		if ( ! is_dir( $parent_dir ) ) {
			if ( ! wp_mkdir_p( $parent_dir ) ) {
				self::send_error( __( 'Could not create cache parent directory. Please check server permissions or see our KB article for manual instructions.', 'wpshadow' ) );
			}
		}

		if ( ! is_dir( $cache_dir ) ) {
			if ( ! wp_mkdir_p( $cache_dir ) ) {
				self::send_error( __( 'Could not create cache directory. Please check server permissions or see our KB article for manual instructions.', 'wpshadow' ) );
			}
		}

		// Verify it's writable now.
		$is_writable = function_exists( 'wp_is_writable' ) ? wp_is_writable( $cache_dir ) : is_writable( $cache_dir );

		if ( ! $is_writable ) {
			// Try to chmod the directory.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod
			if ( @chmod( $cache_dir, 0755 ) ) {
				$is_writable = function_exists( 'wp_is_writable' ) ? wp_is_writable( $cache_dir ) : is_writable( $cache_dir );
			}
		}

		if ( $is_writable ) {
			Activity_Logger::log(
				'cache_permissions_fixed',
				__( 'Cache directory permissions fixed successfully.', 'wpshadow' ),
				'performance'
			);

			self::send_success( array( 'message' => __( 'Cache directory permissions fixed successfully.', 'wpshadow' ) ) );
		} else {
			self::send_error( __( 'Could not fix permissions automatically. Please see our KB article for manual instructions: https://wpshadow.com/kb/cache-directory-permissions', 'wpshadow' ) );
		}
	}
}

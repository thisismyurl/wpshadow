<?php
/**
 * Save Cache Options AJAX Handler
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

class Save_Cache_Options_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hooks for cache option saves.
	 *
	 * @since  1.6047.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_save_cache_options', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle cache option save requests.
	 *
	 * @since 1.6047.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_cache_options', 'manage_options', 'nonce' );

		$cache_pages    = self::get_post_param( 'cache_pages', 'int', 0 );
		$cache_posts    = self::get_post_param( 'cache_posts', 'int', 0 );
		$skip_logged_in = self::get_post_param( 'skip_logged_in', 'int', 0 );
		$auto_clear     = self::get_post_param( 'auto_clear_on_save', 'int', 0 );
		$cache_enabled  = self::get_post_param( 'cache_enabled', 'int', 0 );

		update_option( 'wpshadow_cache_pages', (bool) $cache_pages );
		update_option( 'wpshadow_cache_posts', (bool) $cache_posts );
		update_option( 'wpshadow_skip_logged_in', (bool) $skip_logged_in );
		update_option( 'wpshadow_auto_clear_on_save', (bool) $auto_clear );
		update_option( 'wpshadow_simple_cache_enabled', (bool) $cache_enabled );

		// Log activity (#565: Activity Logging Expansion)
		Activity_Logger::log(
			'cache_settings_changed',
			sprintf(
				__( 'Cache settings updated: enabled=%1$s, pages=%2$s, posts=%3$s, skip_logged_in=%4$s, auto_clear=%5$s', 'wpshadow' ),
				$cache_enabled ? __( 'enabled', 'wpshadow' ) : __( 'disabled', 'wpshadow' ),
				$cache_pages ? __( 'enabled', 'wpshadow' ) : __( 'disabled', 'wpshadow' ),
				$cache_posts ? __( 'enabled', 'wpshadow' ) : __( 'disabled', 'wpshadow' ),
				$skip_logged_in ? __( 'enabled', 'wpshadow' ) : __( 'disabled', 'wpshadow' ),
				$auto_clear ? __( 'enabled', 'wpshadow' ) : __( 'disabled', 'wpshadow' )
			),
			'performance',
			array(
				'cache_enabled'  => (bool) $cache_enabled,
				'cache_pages'    => (bool) $cache_pages,
				'cache_posts'    => (bool) $cache_posts,
				'skip_logged_in' => (bool) $skip_logged_in,
				'auto_clear'     => (bool) $auto_clear,
			)
		);

		self::send_success( array( 'message' => __( 'Cache settings saved successfully.', 'wpshadow' ) ) );
	}
}

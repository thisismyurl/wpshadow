<?php
/**
 * Save Cache Options AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Save_Cache_Options_Handler extends AJAX_Handler_Base {
    public static function register() : void {
        add_action( 'wp_ajax_wpshadow_save_cache_options', [ __CLASS__, 'handle' ] );
    }

    public static function handle() : void {
        self::verify_request( 'wpshadow_cache_options', 'manage_options', 'nonce' );

        $cache_pages    = self::get_post_param( 'cache_pages', 'int', 0 );
        $cache_posts    = self::get_post_param( 'cache_posts', 'int', 0 );
        $skip_logged_in = self::get_post_param( 'skip_logged_in', 'int', 0 );
        $auto_clear     = self::get_post_param( 'auto_clear_on_save', 'int', 0 );

        update_option( 'wpshadow_cache_pages', (bool) $cache_pages );
        update_option( 'wpshadow_cache_posts', (bool) $cache_posts );
        update_option( 'wpshadow_skip_logged_in', (bool) $skip_logged_in );
        update_option( 'wpshadow_auto_clear_on_save', (bool) $auto_clear );

        self::send_success( array( 'message' => __( 'Cache settings saved successfully.', 'wpshadow' ) ) );
    }
}

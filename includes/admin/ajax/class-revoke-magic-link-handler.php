<?php
/**
 * Revoke Magic Link AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Revoke_Magic_Link_Handler extends AJAX_Handler_Base {
    public static function register() : void {
        add_action( 'wp_ajax_wpshadow_revoke_magic_link', [ __CLASS__, 'handle' ] );
    }

    public static function handle() : void {
        self::verify_request( 'wpshadow_magic_link_nonce', 'manage_options', 'nonce' );

        $token = self::get_post_param( 'token', 'key', '', true );

        $magic_links = get_option( 'wpshadow_magic_links', array() );
        if ( isset( $magic_links[ $token ] ) ) {
            unset( $magic_links[ $token ] );
            update_option( 'wpshadow_magic_links', $magic_links );
            self::send_success( array( 'message' => __( 'Magic link revoked successfully.', 'wpshadow' ) ) );
        } else {
            self::send_error( __( 'Magic link not found.', 'wpshadow' ) );
        }
    }
}

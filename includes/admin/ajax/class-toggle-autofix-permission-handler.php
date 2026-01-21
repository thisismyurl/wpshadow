<?php
/**
 * Toggle Autofix Permission AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Toggle_Autofix_Permission_Handler extends AJAX_Handler_Base {
    public static function register() : void {
        add_action( 'wp_ajax_wpshadow_toggle_autofix_permission', [ __CLASS__, 'handle' ] );
    }

    public static function handle() : void {
        self::verify_request( 'wpshadow_autofix_permission', 'manage_options', 'nonce' );

        $finding_id = self::get_post_param( 'finding_id', 'key', '', true );
        $enabled    = self::get_post_param( 'enabled', 'bool', false );

        $permissions = get_option( 'wpshadow_autofix_permissions', array() );

        if ( $enabled ) {
            $permissions[ $finding_id ] = true;
        } else {
            unset( $permissions[ $finding_id ] );
        }

        update_option( 'wpshadow_autofix_permissions', $permissions );

        self::send_success( array(
            'message' => $enabled ? __( 'Auto-fix enabled for this type.', 'wpshadow' ) : __( 'Auto-fix disabled for this type.', 'wpshadow' ),
            'enabled' => (bool) $enabled,
        ) );
    }
}

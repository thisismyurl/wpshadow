<?php
/**
 * Autofix Finding AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Autofix_Finding_Handler extends AJAX_Handler_Base {
    public static function register() : void {
        add_action( 'wp_ajax_wpshadow_autofix_finding', [ __CLASS__, 'handle' ] );
    }

    public static function handle() : void {
        self::verify_request( 'wpshadow_autofix', 'manage_options', 'nonce' );

        $finding_id = self::get_post_param( 'finding_id', 'text', '' );
        $result = \wpshadow_attempt_autofix( $finding_id );

        if ( is_array( $result ) && ! empty( $result['success'] ) ) {
            // Log the fix
            \wpshadow_log_finding_action( $finding_id, 'auto_fixed', $result['message'] ?? '' );
            self::send_success( $result );
        } else {
            self::send_error( $result['message'] ?? __( 'Auto-fix failed.', 'wpshadow' ), $result );
        }
    }
}

<?php
/**
 * Change Finding Status AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Change_Finding_Status_Handler extends AJAX_Handler_Base {
    public static function register() : void {
        add_action( 'wp_ajax_wpshadow_change_finding_status', [ __CLASS__, 'handle' ] );
    }

    public static function handle() : void {
        self::verify_request( 'wpshadow_kanban', 'manage_options', 'nonce' );

        $finding_id = self::get_post_param( 'finding_id', 'key', '', true );
        $new_status = self::get_post_param( 'new_status', 'key', '', true );

        $valid_statuses = array( 'detected', 'ignored', 'manual', 'automated', 'fixed' );
        if ( ! in_array( $new_status, $valid_statuses, true ) ) {
            self::send_error( __( 'Invalid status.', 'wpshadow' ) );
        }

        $status_manager = new \WPShadow\Core\Finding_Status_Manager();
        $status_manager->set_finding_status( $finding_id, $new_status );

        // Log the action
        \wpshadow_log_finding_action( $finding_id, 'status_changed', "Status changed to: {$new_status}" );

        self::send_success( array(
            'message'    => __( 'Finding status updated.', 'wpshadow' ),
            'finding_id' => $finding_id,
            'new_status' => $new_status,
        ) );
    }
}

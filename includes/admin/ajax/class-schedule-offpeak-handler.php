<?php

/**
 * Schedule Offpeak Operation AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Options_Manager;

if (! defined('ABSPATH')) {
    exit;
}

class Schedule_Offpeak_Handler extends AJAX_Handler_Base
{
    public static function register(): void
    {
        add_action('wp_ajax_wpshadow_schedule_offpeak', [__CLASS__, 'handle']);
    }

    public static function handle(): void
    {
        self::verify_request('wpshadow_offpeak', 'manage_options', 'nonce');

        $operation_type = self::get_post_param('operation_type', 'key', '', true);
        $email          = self::get_post_param('email', 'email', '', true);

        $scheduled = Options_Manager::get_array('wpshadow_scheduled_offpeak', []);
        $scheduled[] = array(
            'operation_type' => $operation_type,
            'scheduled_at'   => current_time('timestamp'),
            'user_email'     => $email,
        );
        update_option('wpshadow_scheduled_offpeak', $scheduled);

        if (! wp_next_scheduled('wpshadow_run_offpeak_operations')) {
            $tomorrow_2am = strtotime('tomorrow 2:00');
            wp_schedule_single_event($tomorrow_2am, 'wpshadow_run_offpeak_operations');
        }

        self::send_success(array(
            'message'        => __('Operation scheduled for off-peak hours.', 'wpshadow'),
            'operation_type' => $operation_type,
        ));
    }
}

<?php
/**
 * jQuery Migrate Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

class Treatment_JQuery_Migrate extends Treatment_Base {
    public static function get_finding_id() {
        return 'jquery-migrate-enabled';
    }

    public static function apply() {
        update_option( 'wpshadow_disable_jquery_migrate', true );
        if ( class_exists( '\\WPShadow\\Core\\KPI_Tracker' ) ) {
            KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
        }
        return array(
            'success' => true,
            'message' => 'jQuery Migrate will be disabled on the frontend.',
        );
    }

    public static function undo() {
        delete_option( 'wpshadow_disable_jquery_migrate' );
        return array(
            'success' => true,
            'message' => 'jQuery Migrate disabling has been reverted.',
        );
    }
}

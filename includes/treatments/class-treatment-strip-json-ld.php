<?php
/**
 * Strip JSON-LD Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

class Treatment_Strip_JSON_LD implements Treatment_Interface {
    public static function get_finding_id() {
        return 'json-ld-present';
    }

    public static function can_apply() {
        return true;
    }

    public static function apply() {
        update_option( 'wpshadow_strip_json_ld', true );
        if ( class_exists( '\\WPShadow\\Core\\KPI_Tracker' ) ) {
            KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
        }
        return array(
            'success' => true,
            'message' => 'JSON-LD schema scripts will be stripped from the output.',
        );
    }

    public static function undo() {
        delete_option( 'wpshadow_strip_json_ld' );
        return array(
            'success' => true,
            'message' => 'JSON-LD stripping disabled.',
        );
    }
}

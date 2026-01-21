<?php
/**
 * Strip Speculation Rules Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

class Treatment_Strip_SpeculationRules extends Treatment_Base {
    public static function get_finding_id() {
        return 'speculation-rules-present';
    }

    public static function apply() {
        update_option( 'wpshadow_strip_speculationrules', true );
        if ( class_exists( '\\WPShadow\\Core\\KPI_Tracker' ) ) {
            KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
        }
        return array(
            'success' => true,
            'message' => 'Speculation Rules scripts will be stripped from the output.',
        );
    }

    public static function undo() {
        delete_option( 'wpshadow_strip_speculationrules' );
        return array(
            'success' => true,
            'message' => 'Speculation Rules stripping disabled.',
        );
    }
}

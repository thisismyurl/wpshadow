<?php
/**
 * Disaster Recovery RTO Diagnostic
 *
 * Checks if Recovery Time Objective is achievable in testing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Reliability
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Reliability;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Disaster Recovery RTO Diagnostic Class
 *
 * Validates that Recovery Time Objective targets are achievable.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Disaster_Recovery_RTO extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'disaster-recovery-rto';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Disaster Recovery RTO';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'RTO achievable in testing';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'reliability';

    /**
     * Run the diagnostic check.
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if RTO target is defined
        $rto_target = (int) get_option( 'wpshadow_rto_target_minutes' ) ?? 0;

        if ( ! $rto_target ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'RTO target not defined. Establish recovery time objective.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/disaster-recovery-rto',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if actual RTO from tests
        $actual_rto = (int) get_option( 'wpshadow_last_dr_test_rto_minutes' ) ?? 0;

        if ( ! $actual_rto ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'RTO not tested. Run DR test to validate recovery capability.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 70,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/disaster-recovery-rto',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if actual RTO exceeds target
        if ( $actual_rto > $rto_target ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: minutes */
                    __( 'Actual RTO (%d min) exceeds target (%d min). Improve recovery procedures.', 'wpshadow' ),
                    $actual_rto,
                    $rto_target
                ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/disaster-recovery-rto',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}

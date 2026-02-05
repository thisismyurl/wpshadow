<?php
/**
 * Disaster Recovery RPO Diagnostic
 *
 * Checks if Recovery Point Objective is achievable in testing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Reliability
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Reliability;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Disaster Recovery RPO Diagnostic Class
 *
 * Validates that Recovery Point Objective targets are achievable.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Disaster_Recovery_RPO extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'disaster-recovery-rpo';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Disaster Recovery RPO';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'RPO achievable in testing';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'reliability';

    /**
     * Run the diagnostic check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if RPO target is defined
        $rpo_target = (int) get_option( 'wpshadow_rpo_target_minutes' ) ?? 0;

        if ( ! $rpo_target ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'RPO target not defined. Establish recovery point objective.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/disaster-recovery-rpo',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if actual RPO from tests
        $actual_rpo = (int) get_option( 'wpshadow_last_dr_test_rpo_minutes' ) ?? 0;

        if ( ! $actual_rpo ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'RPO not tested. Run DR test to validate data recovery capability.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 70,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/disaster-recovery-rpo',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if actual RPO exceeds target
        if ( $actual_rpo > $rpo_target ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: minutes */
                    __( 'Actual RPO (%d min) exceeds target (%d min). Increase backup frequency.', 'wpshadow' ),
                    $actual_rpo,
                    $rpo_target
                ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/disaster-recovery-rpo',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}

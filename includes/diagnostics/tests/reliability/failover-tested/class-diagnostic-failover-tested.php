<?php
/**
 * Failover Tested Diagnostic
 *
 * Checks if failover capability has been tested and validated.
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
 * Failover Tested Diagnostic Class
 *
 * Validates that failover capability has been tested and validated in practice.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Failover_Tested extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'failover-tested';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Failover Tested';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Failover capability tested and validated';

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
        // Check if failover testing has been documented
        $failover_test_date = get_option( 'wpshadow_failover_last_tested' );

        if ( ! $failover_test_date ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Failover has never been tested. Run a test to validate disaster recovery procedures.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 75,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/failover-tested',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check how old the last test was
        $test_timestamp = (int) $failover_test_date;
        $current_time   = time();
        $days_since_test = floor( ( $current_time - $test_timestamp ) / 86400 );

        if ( $days_since_test > 90 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: days */
                    __( 'Failover last tested %d days ago. Run periodic tests to ensure procedures remain current.', 'wpshadow' ),
                    $days_since_test
                ),
                'severity'      => 'medium',
                'threat_level'  => 40,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/failover-tested',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if last test was successful
        $last_test_success = get_option( 'wpshadow_failover_last_test_success' );

        if ( ! $last_test_success ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Last failover test failed. Review logs and troubleshoot.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 80,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/failover-tested',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}

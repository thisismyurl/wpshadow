<?php
/**
 * SLA Monitoring Dashboard Diagnostic
 *
 * Checks if real-time SLA tracking is visible in dashboard.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Enterprise
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Enterprise;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SLA Monitoring Dashboard Diagnostic Class
 *
 * Validates that SLA monitoring dashboard is active and real-time.
 *
 * @since 0.6093.1200
 */
class Diagnostic_SLA_Monitoring_Dashboard extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'sla-monitoring-dashboard';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'SLA Monitoring Dashboard';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Real-time SLA tracking visible';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'enterprise';

    /**
     * Run the diagnostic check.
     *
     * @since 0.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if SLA dashboard is configured
        $sla_dashboard_enabled = get_option( 'wpshadow_sla_dashboard_enabled' );

        if ( ! $sla_dashboard_enabled ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'SLA monitoring dashboard not configured. Enable for real-time SLA tracking.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 50,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/sla-monitoring-dashboard?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        // Check if SLAs are defined
        $slas_defined = (int) get_option( 'wpshadow_slas_count' ) ?? 0;

        if ( $slas_defined === 0 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'No SLAs defined. Define service level agreements to track.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 45,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/sla-monitoring-dashboard?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // No issue found
    }
}

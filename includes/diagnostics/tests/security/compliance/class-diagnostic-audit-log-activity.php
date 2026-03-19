<?php
/**
 * Audit Log Activity Diagnostic
 *
 * Critical for corporate/compliance: ensures admin actions are being logged for audit trails.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Audit Log Activity Diagnostic
 *
 * Verifies admin action logging is active and entries are being recorded.
 * High priority for corporate compliance and regulatory requirements.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Audit_Log_Activity extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'audit-log-activity';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Audit Log Activity';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Ensures admin actions are logged for compliance and audit trails';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'compliance-security';

    /**
     * Personas this diagnostic is critical for
     *
     * @var array
     */
    protected static $personas = array(
        'corporate',
        'enterprise-corp',
        'agency',
    );

    /**
     * Run the diagnostic check
     *
     * @since 1.6093.1200
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Check if audit logging is active
        if ( ! function_exists( 'wpshadow_log_activity' ) && ! class_exists( 'WP_Activity_Log' ) && ! class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => __( 'No audit logging plugin detected. Admin actions are not being tracked. Required for compliance.', 'wpshadow' ),
                'severity'     => 'critical',
                'threat_level' => 100,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/audit-logging',
                'personas'     => self::$personas,
                'compliance_impact' => __( 'Failure to maintain audit logs may result in failed security audits (SOC2, ISO27001, HIPAA)', 'wpshadow' ),
            );
        }

        // Check if logs are being created.
        if ( ! class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
            'description'  => __( 'Audit logger is not available. Logging is not properly initialized.', 'wpshadow' ),
                'severity'     => 'critical',
                'threat_level' => 95,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/audit-logging',
                'personas'     => self::$personas,
            );
        }

        $activity_log = get_option( \WPShadow\Core\Activity_Logger::OPTION_NAME, array() );

        if ( ! is_array( $activity_log ) || empty( $activity_log ) ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => __( 'No audit logs are currently stored. Check that logging is enabled and running.', 'wpshadow' ),
                'severity'     => 'high',
                'threat_level' => 80,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/audit-logging',
                'personas'     => self::$personas,
            );
        }

        // Check if recent logs exist (within last 24 hours).
        $recent_logs = 0;
        $cutoff      = time() - DAY_IN_SECONDS;

        foreach ( $activity_log as $entry ) {
            $entry_time = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : 0;
            if ( $entry_time > $cutoff ) {
                ++$recent_logs;
            }
        }

        if ( ! $recent_logs ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => __( 'No audit logs created in the last 24 hours. Check that logging is enabled and hooks are firing.', 'wpshadow' ),
                'severity'     => 'high',
                'threat_level' => 80,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/audit-logging',
                'personas'     => self::$personas,
            );
        }

        // Check log retention period
        $retention_days = get_option( 'wpshadow_audit_log_retention_days', 365 );

        if ( $retention_days < 90 ) {
            return array(
                'id'           => self::$slug,
                'title'        => self::$title,
                'description'  => sprintf(
                    /* translators: %d: number of days */
                    __( 'Audit logs retained for only %d days. Most compliance standards require 1-7 years.', 'wpshadow' ),
                    $retention_days
                ),
                'severity'     => 'high',
                'threat_level' => 85,
                'auto_fixable' => false,
                'kb_link'      => 'https://wpshadow.com/kb/audit-logging',
                'personas'     => self::$personas,
                'compliance_impact' => sprintf(
                    /* translators: %d: years */
                    __( 'Consider setting retention to %d years per regulatory requirements', 'wpshadow' ),
                    7
                ),
            );
        }

        return null; // Logging is properly configured
    }
}

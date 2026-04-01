<?php
/**
 * Alerting System Diagnostic
 *
 * Checks if PagerDuty or equivalent alerting system is configured.
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
 * Alerting System Diagnostic Class
 *
 * Validates that alerting system (PagerDuty/similar) is configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Alerting_System extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'alerting-system';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Alerting System';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'PagerDuty or equivalent configured';

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
        // Check for alerting service API keys in wp-config or environment
        $pagerduty_key = defined( 'PAGERDUTY_API_KEY' ) ? PAGERDUTY_API_KEY : getenv( 'PAGERDUTY_API_KEY' );
        $opsgenie_key  = defined( 'OPSGENIE_API_KEY' ) ? OPSGENIE_API_KEY : getenv( 'OPSGENIE_API_KEY' );
        $victorops_key = defined( 'VICTOROPS_API_KEY' ) ? VICTOROPS_API_KEY : getenv( 'VICTOROPS_API_KEY' );

        // Check for alerting plugins
        $alerting_plugins = array(
            'wp-pagerduty/wp-pagerduty.php'  => 'PagerDuty',
            'opsgenie/opsgenie.php'          => 'Opsgenie',
        );

        $found_alerting = array();
        foreach ( $alerting_plugins as $plugin_path => $name ) {
            if ( is_plugin_active( $plugin_path ) ) {
                $found_alerting[] = $name;
            }
        }

        // Check WordPress options for alerting configuration
        $pagerduty_service_key = get_option( 'wpshadow_pagerduty_service_key' );
        $slack_webhook         = get_option( 'wpshadow_slack_webhook' );
        $alert_email           = get_option( 'wpshadow_alert_email' );

        if ( $pagerduty_key || $opsgenie_key || $victorops_key || $pagerduty_service_key || $found_alerting ) {
            $found_alerting[] = 'Enterprise alerting configured';
        } elseif ( $slack_webhook || $alert_email ) {
            // Basic alerting via webhook/email
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Basic alerting detected but no enterprise incident management. Consider PagerDuty/Opsgenie for on-call rotation and escalation policies.', 'wpshadow' ),
                'severity'      => 'medium',
                'threat_level'  => 45,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/alerting-system?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        if ( empty( $found_alerting ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Adding an alerting system means getting immediately notified when something breaks (like a fire alarm that rings when smoke is detected). Tools like PagerDuty or Opsgenie can wake up your on-call team at 3am if your site goes down.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 70,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/alerting-system?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // Alerting system detected
    }
}

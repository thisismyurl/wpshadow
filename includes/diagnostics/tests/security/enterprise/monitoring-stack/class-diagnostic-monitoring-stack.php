<?php
/**
 * Monitoring Stack Diagnostic
 *
 * Checks if Prometheus/Grafana or equivalent monitoring is active.
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
 * Monitoring Stack Diagnostic Class
 *
 * Validates that monitoring infrastructure (Prometheus/Grafana) is active.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Monitoring_Stack extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'monitoring-stack';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Monitoring Stack';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Prometheus/Grafana or equivalent active';

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
        // Check for monitoring agent files/plugins
        $monitoring_indicators = array(
            'new-relic/new-relic.php'                     => 'New Relic',
            'query-monitor/query-monitor.php'             => 'Query Monitor',
            'application-insights/application-insights.php' => 'Application Insights',
        );

        $found_monitoring = array();
        foreach ( $monitoring_indicators as $plugin_path => $name ) {
            if ( is_plugin_active( $plugin_path ) ) {
                $found_monitoring[] = $name;
            }
        }

        // Check for monitoring endpoint configuration
        $prometheus_endpoint = get_option( 'wpshadow_prometheus_endpoint' );
        $grafana_endpoint    = get_option( 'wpshadow_grafana_endpoint' );
        $datadog_key         = defined( 'DATADOG_API_KEY' ) ? DATADOG_API_KEY : get_option( 'wpshadow_datadog_api_key' );

        if ( $prometheus_endpoint || $grafana_endpoint || $datadog_key ) {
            $found_monitoring[] = 'External monitoring configured';
        }

        // Check for metrics export plugin
        if ( function_exists( 'prometheus_metrics_init' ) ) {
            $found_monitoring[] = 'Prometheus metrics exporter';
        }

        if ( empty( $found_monitoring ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Adding a monitoring system helps you watch your site\'s health in real-time (like security cameras and a dashboard showing everything at once). These tools track memory usage, response times, and potential problems before visitors notice them. Popular options include Prometheus/Grafana, New Relic, or Datadog.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 65,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/monitoring-stack?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // Monitoring detected
    }
}

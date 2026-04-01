<?php
/**
 * Log Aggregation Diagnostic
 *
 * Checks if ELK/Splunk is collecting logs from all sources.
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
 * Log Aggregation Diagnostic Class
 *
 * Validates that log aggregation (ELK/Splunk) is configured for all systems.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Log_Aggregation extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'log-aggregation';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Log Aggregation';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'ELK/Splunk collecting logs';

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
        // Check for log shipping configuration
        $log_config_files = array(
            '/etc/filebeat/filebeat.yml'   => 'Filebeat',
            '/etc/logstash/logstash.yml'   => 'Logstash',
            '/etc/fluentd/fluent.conf'     => 'Fluentd',
            '/etc/td-agent/td-agent.conf'  => 'Fluentd (td-agent)',
        );

        $found_log_shippers = array();
        foreach ( $log_config_files as $path => $shipper ) {
            if ( @file_exists( $path ) ) {
                $found_log_shippers[] = $shipper;
            }
        }

        // Check for log aggregation plugins
        $log_plugins = array(
            'logentries/logentries.php'           => 'Logentries',
            'loggly/loggly.php'                  => 'Loggly',
            'papertrail/papertrail.php'          => 'Papertrail',
        );

        foreach ( $log_plugins as $plugin_path => $name ) {
            if ( is_plugin_active( $plugin_path ) ) {
                $found_log_shippers[] = $name;
            }
        }

        // Check for environment variables indicating log aggregation
        $elasticsearch_host = getenv( 'ELASTICSEARCH_HOST' );
        $splunk_host        = getenv( 'SPLUNK_HOST' );
        $sumologic_endpoint = get_option( 'wpshadow_sumologic_endpoint' );

        if ( $elasticsearch_host || $splunk_host || $sumologic_endpoint ) {
            $found_log_shippers[] = 'External log aggregation configured';
        }

        if ( empty( $found_log_shippers ) ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => __( 'Collecting all your site logs in one place helps you spot patterns and problems (like a detective\'s notebook keeping track of everything that happens). If something goes wrong, you can trace what happened. Tools like ELK Stack or Splunk make this easier.', 'wpshadow' ),
                'severity'      => 'high',
                'threat_level'  => 60,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/log-aggregation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
                'persona'       => 'enterprise-corp',
            );
        }

        return null; // Log aggregation detected
    }
}

<?php
/**
 * Log Aggregation Diagnostic
 *
 * Checks if ELK/Splunk or equivalent log aggregation is collecting logs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6035.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log Aggregation Diagnostic Class
 *
 * Detects if centralized log aggregation system is configured
 * for enterprise-level log management and analysis.
 *
 * @since 1.6035.1445
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
	protected static $description = 'Checks if ELK/Splunk or equivalent is collecting logs';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'logging';

	/**
	 * Primary persona
	 *
	 * @var string
	 */
	protected static $persona = 'enterprise-corp';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for log aggregation configuration.
		$log_aggregation_enabled = get_option( 'wpshadow_log_aggregation_enabled', false );
		$log_aggregation_service = get_option( 'wpshadow_log_aggregation_service', '' );

		// Check for ELK Stack (Elasticsearch, Logstash, Kibana).
		$has_elasticsearch = defined( 'ELASTICSEARCH_HOST' ) || 
		                     get_option( 'elasticsearch_host', '' ) !== '' ||
		                     is_plugin_active( 'elasticpress/elasticpress.php' );

		$has_logstash = defined( 'LOGSTASH_ENDPOINT' ) || 
		                get_option( 'logstash_endpoint', '' ) !== '';

		$has_elk = $has_elasticsearch && $has_logstash;

		// Check for Splunk.
		$has_splunk = defined( 'SPLUNK_HEC_TOKEN' ) || 
		              defined( 'SPLUNK_ENDPOINT' ) ||
		              get_option( 'splunk_hec_token', '' ) !== '';

		// Check for popular log management services.
		$log_services = array();

		if ( defined( 'LOGGLY_TOKEN' ) || get_option( 'loggly_token', '' ) ) {
			$log_services[] = 'Loggly';
		}

		if ( defined( 'PAPERTRAIL_ENDPOINT' ) || get_option( 'papertrail_endpoint', '' ) ) {
			$log_services[] = 'Papertrail';
		}

		if ( defined( 'DATADOG_API_KEY' ) || get_option( 'datadog_api_key', '' ) ) {
			$log_services[] = 'Datadog';
		}

		if ( defined( 'NEW_RELIC_APPNAME' ) ) {
			$log_services[] = 'New Relic';
		}

		if ( defined( 'CLOUDWATCH_LOG_GROUP' ) || get_option( 'cloudwatch_log_group', '' ) ) {
			$log_services[] = 'AWS CloudWatch';
		}

		if ( defined( 'STACKDRIVER_PROJECT_ID' ) || get_option( 'stackdriver_project_id', '' ) ) {
			$log_services[] = 'Google Cloud Logging';
		}

		if ( defined( 'SENTRY_DSN' ) ) {
			$log_services[] = 'Sentry';
		}

		// Check for logging plugins.
		$logging_plugins = array(
			'wp-log-viewer/wp-log-viewer.php'               => 'WP Log Viewer',
			'log-http-requests/log-http-requests.php'       => 'Log HTTP Requests',
			'query-monitor/query-monitor.php'               => 'Query Monitor',
			'debug-bar/debug-bar.php'                       => 'Debug Bar',
			'wp-sentry-integration/wp-sentry.php'           => 'WP Sentry',
		);

		$has_logging_plugin = false;
		$active_logging_plugins = array();

		foreach ( $logging_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_logging_plugin = true;
				$active_logging_plugins[] = $plugin_name;
			}
		}

		// Check WP_DEBUG_LOG setting.
		$wp_debug_log_enabled = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$debug_log_exists = file_exists( WP_CONTENT_DIR . '/debug.log' );

		// Check for custom error log location.
		$custom_error_log = ini_get( 'error_log' );
		$has_custom_log = ! empty( $custom_error_log ) && $custom_error_log !== 'syslog';

		// Check log file size (if debug.log exists).
		$debug_log_size = 0;
		if ( $debug_log_exists ) {
			$debug_log_size = filesize( WP_CONTENT_DIR . '/debug.log' );
		}

		// Check for log rotation.
		$has_log_rotation = get_option( 'wpshadow_log_rotation_enabled', false ) ||
		                    file_exists( '/etc/logrotate.d/wordpress' );

		// Check log retention policy.
		$log_retention_days = get_option( 'wpshadow_log_retention_days', 0 );

		// Determine if enterprise log aggregation exists.
		$has_enterprise_logging = $has_elk || 
		                          $has_splunk || 
		                          ! empty( $log_services ) ||
		                          $log_aggregation_enabled;

		// Evaluate issues.
		if ( ! $has_enterprise_logging ) {
			$issues[] = __( 'No centralized log aggregation system configured', 'wpshadow' );
			$issues[] = __( 'Log analysis and troubleshooting will be difficult', 'wpshadow' );
		}

		if ( ! $wp_debug_log_enabled && ! $has_custom_log ) {
			$issues[] = __( 'WP_DEBUG_LOG not enabled - errors not being logged', 'wpshadow' );
		}

		if ( $debug_log_size > 10485760 ) { // 10MB.
			$size_mb = round( $debug_log_size / 1048576, 1 );
			$issues[] = sprintf(
				/* translators: %s: log file size in MB */
				__( 'Debug log file is %sMB - consider log rotation', 'wpshadow' ),
				$size_mb
			);
		}

		if ( $has_enterprise_logging && ! $has_log_rotation ) {
			$issues[] = __( 'Log aggregation configured but log rotation not enabled', 'wpshadow' );
		}

		if ( $has_enterprise_logging && $log_retention_days === 0 ) {
			$issues[] = __( 'No log retention policy defined', 'wpshadow' );
		} elseif ( $log_retention_days > 0 && $log_retention_days < 90 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Log retention only %d days (recommend 90+ for compliance)', 'wpshadow' ),
				$log_retention_days
			);
		}

		// Check structured logging.
		$has_structured_logging = get_option( 'wpshadow_structured_logging_enabled', false ) ||
		                          defined( 'WP_JSON_LOGGING' );

		if ( $has_enterprise_logging && ! $has_structured_logging ) {
			$issues[] = __( 'Structured logging (JSON format) not enabled', 'wpshadow' );
		}

		// Check log shipping status.
		$last_log_ship = get_option( 'wpshadow_last_log_ship_timestamp', 0 );
		$hours_since_ship = $last_log_ship > 0 
			? ( time() - $last_log_ship ) / HOUR_IN_SECONDS 
			: 9999;

		if ( $has_enterprise_logging && $hours_since_ship > 24 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hours */
				__( 'Logs not shipped to aggregation service in %d+ hours', 'wpshadow' ),
				floor( $hours_since_ship )
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$all_services = array_merge(
			$has_elk ? array( 'ELK Stack' ) : array(),
			$has_splunk ? array( 'Splunk' ) : array(),
			$log_services,
			$active_logging_plugins
		);

		$description = sprintf(
			/* translators: %s: list of configured logging services */
			__( 'Log aggregation not fully configured. %s', 'wpshadow' ),
			! empty( $all_services ) 
				? sprintf( __( 'Currently configured: %s', 'wpshadow' ), implode( ', ', $all_services ) )
				: __( 'No log aggregation tools detected.', 'wpshadow' )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/log-aggregation',
			'issues'       => $issues,
			'persona'      => self::$persona,
			'context'      => array(
				'has_enterprise_logging'  => $has_enterprise_logging,
				'has_elk'                 => $has_elk,
				'has_splunk'              => $has_splunk,
				'log_services'            => $log_services,
				'active_logging_plugins'  => $active_logging_plugins,
				'wp_debug_log_enabled'    => $wp_debug_log_enabled,
				'debug_log_size_mb'       => $debug_log_size > 0 ? round( $debug_log_size / 1048576, 1 ) : 0,
				'has_log_rotation'        => $has_log_rotation,
				'log_retention_days'      => $log_retention_days,
				'has_structured_logging'  => $has_structured_logging,
				'hours_since_ship'        => floor( $hours_since_ship ),
			),
		);
	}
}

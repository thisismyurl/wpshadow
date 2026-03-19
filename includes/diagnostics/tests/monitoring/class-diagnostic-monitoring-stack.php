<?php
/**
 * Monitoring Stack Diagnostic
 *
 * Checks if Prometheus/Grafana or equivalent monitoring stack is active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Monitoring Stack Diagnostic Class
 *
 * Detects if comprehensive monitoring stack is configured
 * for metrics collection, visualization, and alerting.
 *
 * @since 1.6093.1200
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
	protected static $description = 'Checks if Prometheus/Grafana or equivalent monitoring stack is active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise-monitoring';

	/**
	 * Primary persona
	 *
	 * @var string
	 */
	protected static $persona = 'enterprise-corp';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for Prometheus.
		$has_prometheus = defined( 'PROMETHEUS_PUSHGATEWAY' ) || 
		                  defined( 'PROMETHEUS_ENDPOINT' ) ||
		                  get_option( 'wpshadow_prometheus_enabled', false ) ||
		                  get_option( 'prometheus_endpoint', '' ) !== '';

		// Check for Grafana.
		$has_grafana = defined( 'GRAFANA_ENDPOINT' ) || 
		               defined( 'GRAFANA_API_KEY' ) ||
		               get_option( 'grafana_dashboard_url', '' ) !== '' ||
		               get_option( 'wpshadow_grafana_enabled', false );

		// Check for complete Prometheus + Grafana stack.
		$has_prom_graf_stack = $has_prometheus && $has_grafana;

		// Check for alternative monitoring stacks.
		$monitoring_stacks = array();

		if ( defined( 'DATADOG_API_KEY' ) || get_option( 'datadog_api_key', '' ) ) {
			$monitoring_stacks[] = 'Datadog';
		}

		if ( defined( 'NEW_RELIC_APPNAME' ) || defined( 'NEWRELIC_LICENSE_KEY' ) ) {
			$monitoring_stacks[] = 'New Relic';
		}

		if ( defined( 'ELASTIC_APM_ENABLED' ) || get_option( 'elastic_apm_enabled', false ) ) {
			$monitoring_stacks[] = 'Elastic APM';
		}

		if ( defined( 'DYNATRACE_TENANT' ) || get_option( 'dynatrace_tenant', '' ) ) {
			$monitoring_stacks[] = 'Dynatrace';
		}

		if ( defined( 'APPDYNAMICS_CONTROLLER' ) ) {
			$monitoring_stacks[] = 'AppDynamics';
		}

		if ( get_option( 'wpshadow_cloudwatch_enabled', false ) || defined( 'AWS_CLOUDWATCH_ENABLED' ) ) {
			$monitoring_stacks[] = 'AWS CloudWatch';
		}

		if ( get_option( 'wpshadow_azure_monitor_enabled', false ) ) {
			$monitoring_stacks[] = 'Azure Monitor';
		}

		if ( get_option( 'wpshadow_stackdriver_enabled', false ) || defined( 'STACKDRIVER_PROJECT_ID' ) ) {
			$monitoring_stacks[] = 'Google Cloud Monitoring';
		}

		// Check for monitoring plugins.
		$monitoring_plugins = array(
			'query-monitor/query-monitor.php'               => 'Query Monitor',
			'application-performance-monitor/apm.php'       => 'Application Performance Monitor',
			'wp-statistics/wp-statistics.php'               => 'WP Statistics',
			'jetpack/jetpack.php'                           => 'Jetpack Stats',
		);

		$active_monitoring_plugins = array();
		foreach ( $monitoring_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_monitoring_plugins[] = $plugin_name;
			}
		}

		// Check metrics collection.
		$metrics_enabled = get_option( 'wpshadow_metrics_collection_enabled', false );
		$metrics_endpoint = get_option( 'wpshadow_metrics_endpoint', '' );

		// Check for custom metrics.
		$custom_metrics_count = get_option( 'wpshadow_custom_metrics_count', 0 );

		// Check dashboard configuration.
		$dashboard_url = get_option( 'wpshadow_monitoring_dashboard_url', '' );
		$has_dashboard = ! empty( $dashboard_url ) || $has_grafana;

		// Check retention policy for metrics.
		$metrics_retention_days = get_option( 'wpshadow_metrics_retention_days', 0 );

		// Check alerting rules.
		$alert_rules_count = get_option( 'wpshadow_monitoring_alert_rules_count', 0 );

		// Determine if enterprise monitoring exists.
		$has_enterprise_monitoring = $has_prom_graf_stack || 
		                             ! empty( $monitoring_stacks );

		// Evaluate issues.
		if ( ! $has_enterprise_monitoring ) {
			$issues[] = __( 'No enterprise monitoring stack configured', 'wpshadow' );
			$issues[] = __( 'Performance metrics and system health not being tracked', 'wpshadow' );
		}

		if ( $has_prometheus && ! $has_grafana ) {
			$issues[] = __( 'Prometheus configured but Grafana dashboard missing', 'wpshadow' );
		}

		if ( $has_enterprise_monitoring && ! $metrics_enabled ) {
			$issues[] = __( 'Monitoring stack configured but metrics collection not enabled', 'wpshadow' );
		}

		if ( $has_enterprise_monitoring && ! $has_dashboard ) {
			$issues[] = __( 'No monitoring dashboard URL configured', 'wpshadow' );
		}

		if ( $has_enterprise_monitoring && $metrics_retention_days === 0 ) {
			$issues[] = __( 'No metrics retention policy defined', 'wpshadow' );
		} elseif ( $metrics_retention_days > 0 && $metrics_retention_days < 30 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Metrics retention only %d days (recommend 30+ for trending)', 'wpshadow' ),
				$metrics_retention_days
			);
		}

		if ( $has_enterprise_monitoring && $alert_rules_count === 0 ) {
			$issues[] = __( 'No monitoring alert rules configured', 'wpshadow' );
		}

		if ( $has_enterprise_monitoring && $custom_metrics_count === 0 ) {
			$issues[] = __( 'No custom business metrics being tracked', 'wpshadow' );
		}

		// Check for key performance metrics.
		$tracking_response_time = get_option( 'wpshadow_tracking_response_time', false );
		$tracking_error_rate = get_option( 'wpshadow_tracking_error_rate', false );
		$tracking_throughput = get_option( 'wpshadow_tracking_throughput', false );

		if ( $has_enterprise_monitoring ) {
			$missing_metrics = array();
			if ( ! $tracking_response_time ) {
				$missing_metrics[] = __( 'response time', 'wpshadow' );
			}
			if ( ! $tracking_error_rate ) {
				$missing_metrics[] = __( 'error rate', 'wpshadow' );
			}
			if ( ! $tracking_throughput ) {
				$missing_metrics[] = __( 'throughput', 'wpshadow' );
			}

			if ( ! empty( $missing_metrics ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of missing metrics */
					__( 'Key metrics not tracked: %s', 'wpshadow' ),
					implode( ', ', $missing_metrics )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$all_stacks = array_merge(
			$has_prom_graf_stack ? array( 'Prometheus + Grafana' ) : array(),
			$monitoring_stacks,
			$active_monitoring_plugins
		);

		$description = sprintf(
			/* translators: %s: list of configured monitoring stacks */
			__( 'Monitoring stack not fully configured. %s', 'wpshadow' ),
			! empty( $all_stacks ) 
				? sprintf( __( 'Currently configured: %s', 'wpshadow' ), implode( ', ', $all_stacks ) )
				: __( 'No monitoring stack detected.', 'wpshadow' )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/monitoring-stack',
			'issues'       => $issues,
			'persona'      => self::$persona,
			'context'      => array(
				'has_enterprise_monitoring'   => $has_enterprise_monitoring,
				'has_prometheus'              => $has_prometheus,
				'has_grafana'                 => $has_grafana,
				'has_prom_graf_stack'         => $has_prom_graf_stack,
				'monitoring_stacks'           => $monitoring_stacks,
				'active_monitoring_plugins'   => $active_monitoring_plugins,
				'metrics_enabled'             => $metrics_enabled,
				'custom_metrics_count'        => $custom_metrics_count,
				'has_dashboard'               => $has_dashboard,
				'dashboard_url'               => $dashboard_url,
				'metrics_retention_days'      => $metrics_retention_days,
				'alert_rules_count'           => $alert_rules_count,
				'tracking_response_time'      => $tracking_response_time,
				'tracking_error_rate'         => $tracking_error_rate,
				'tracking_throughput'         => $tracking_throughput,
			),
		);
	}
}

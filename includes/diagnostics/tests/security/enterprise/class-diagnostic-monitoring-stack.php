<?php
/**
 * Monitoring Stack Diagnostic
 *
 * Checks if comprehensive monitoring infrastructure is in place for enterprise operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1200
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
 * Verifies that a proper monitoring stack (APM, uptime, metrics) is configured for
 * enterprise-grade observability.
 *
 * @since 1.6035.1200
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
	protected static $title = 'Monitoring Stack Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comprehensive monitoring infrastructure is in place for enterprise operations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the monitoring stack diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if monitoring gaps detected, null otherwise.
	 */
	public static function check() {
		$monitoring_components = array();
		$missing_components    = array();
		$warnings              = array();

		// Check for APM solutions (Application Performance Monitoring).
		$apm_active = false;
		if ( defined( 'NEWRELIC_APPNAME' ) || function_exists( 'newrelic_notice_error' ) ) {
			$monitoring_components['apm'] = 'New Relic';
			$apm_active                   = true;
		} elseif ( class_exists( 'Datadog\Trace\Tracer' ) || defined( 'DD_TRACE_ENABLED' ) ) {
			$monitoring_components['apm'] = 'Datadog';
			$apm_active                   = true;
		} elseif ( class_exists( 'Elastic\Apm\ElasticApm' ) ) {
			$monitoring_components['apm'] = 'Elastic APM';
			$apm_active                   = true;
		} elseif ( function_exists( 'appsignal_start' ) ) {
			$monitoring_components['apm'] = 'AppSignal';
			$apm_active                   = true;
		}

		if ( ! $apm_active ) {
			$missing_components[] = __( 'Application Performance Monitoring (APM)', 'wpshadow' );
		}

		// Check for uptime monitoring.
		$uptime_configured = false;
		// Check for common uptime monitoring headers or constants.
		if ( defined( 'UPTIME_ROBOT_API_KEY' ) || defined( 'PINGDOM_API_KEY' ) ) {
			$monitoring_components['uptime'] = 'Uptime monitoring configured';
			$uptime_configured               = true;
		}
		// Check for health check endpoints.
		$health_check_exists = file_exists( ABSPATH . 'health-check.php' ) || 
							   file_exists( ABSPATH . 'healthcheck.php' ) ||
							   file_exists( ABSPATH . 'wp-content/health.php' );
		if ( $health_check_exists ) {
			$monitoring_components['health_check'] = 'Health check endpoint exists';
			$uptime_configured                     = true;
		}

		if ( ! $uptime_configured ) {
			$missing_components[] = __( 'Uptime monitoring system', 'wpshadow' );
		}

		// Check for metrics collection.
		$metrics_active = false;
		if ( class_exists( 'Prometheus\CollectorRegistry' ) ) {
			$monitoring_components['metrics'] = 'Prometheus';
			$metrics_active                   = true;
		} elseif ( defined( 'STATSD_HOST' ) || function_exists( 'statsd_increment' ) ) {
			$monitoring_components['metrics'] = 'StatsD';
			$metrics_active                   = true;
		} elseif ( defined( 'GRAPHITE_HOST' ) ) {
			$monitoring_components['metrics'] = 'Graphite';
			$metrics_active                   = true;
		}

		if ( ! $metrics_active ) {
			$missing_components[] = __( 'Metrics collection system', 'wpshadow' );
		}

		// Check for error tracking.
		$error_tracking_active = false;
		if ( class_exists( 'Sentry\SentrySdk' ) || function_exists( 'sentry_init' ) ) {
			$monitoring_components['error_tracking'] = 'Sentry';
			$error_tracking_active                   = true;
		} elseif ( class_exists( 'Bugsnag\Client' ) ) {
			$monitoring_components['error_tracking'] = 'Bugsnag';
			$error_tracking_active                   = true;
		} elseif ( class_exists( 'Rollbar\Rollbar' ) ) {
			$monitoring_components['error_tracking'] = 'Rollbar';
			$error_tracking_active                   = true;
		} elseif ( class_exists( 'Airbrake\Notifier' ) ) {
			$monitoring_components['error_tracking'] = 'Airbrake';
			$error_tracking_active                   = true;
		}

		if ( ! $error_tracking_active ) {
			$missing_components[] = __( 'Error tracking system', 'wpshadow' );
		}

		// Check for log management.
		$log_management_active = false;
		if ( defined( 'LOGGLY_TOKEN' ) || defined( 'LOGENTRIES_TOKEN' ) ) {
			$monitoring_components['log_management'] = 'Log management configured';
			$log_management_active                   = true;
		} elseif ( class_exists( 'Monolog\Handler\LogglyHandler' ) || class_exists( 'Monolog\Handler\ElasticsearchHandler' ) ) {
			$monitoring_components['log_management'] = 'Monolog with external handler';
			$log_management_active                   = true;
		}

		if ( ! $log_management_active ) {
			$warnings[] = __( 'No centralized log management detected', 'wpshadow' );
		}

		// Check WordPress debug settings (should be off in production with proper monitoring).
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$warnings[] = __( 'WP_DEBUG is enabled - should rely on APM instead in production', 'wpshadow' );
		}

		// If we're missing 3 or more core components, it's a critical issue.
		if ( count( $missing_components ) >= 3 ) {
			$description = sprintf(
				/* translators: 1: number of missing components, 2: list of missing components */
				__( 'Enterprise monitoring stack is incomplete. Missing %1$d critical components: %2$s', 'wpshadow' ),
				count( $missing_components ),
				implode( ', ', $missing_components )
			);

			if ( ! empty( $warnings ) ) {
				$description .= ' ' . __( 'Additional concerns:', 'wpshadow' ) . ' ' . implode( ', ', $warnings );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/monitoring-stack',
				'context'      => array(
					'monitoring_components' => $monitoring_components,
					'missing_components'    => $missing_components,
					'warnings'              => $warnings,
				),
			);
		}

		// If missing 1-2 components, it's a medium severity issue.
		if ( ! empty( $missing_components ) ) {
			$description = sprintf(
				/* translators: %s: list of missing components */
				__( 'Monitoring stack could be improved. Missing: %s', 'wpshadow' ),
				implode( ', ', $missing_components )
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/monitoring-stack',
				'context'      => array(
					'monitoring_components' => $monitoring_components,
					'missing_components'    => $missing_components,
					'warnings'              => $warnings,
				),
			);
		}

		// If we have all components but warnings, it's a low severity issue.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Monitoring stack is configured but has minor issues: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/monitoring-stack',
				'context'      => array(
					'monitoring_components' => $monitoring_components,
					'warnings'              => $warnings,
				),
			);
		}

		return null; // No issues found - comprehensive monitoring stack is in place.
	}
}

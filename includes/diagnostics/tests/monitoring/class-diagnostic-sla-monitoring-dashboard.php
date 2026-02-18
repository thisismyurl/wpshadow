<?php
/**
 * SLA Monitoring Dashboard Diagnostic
 *
 * Checks if real-time SLA monitoring dashboard is configured for enterprise tracking.
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
 * SLA Monitoring Dashboard Diagnostic Class
 *
 * Detects if SLA monitoring and tracking is properly configured
 * for enterprise-level service level agreement monitoring.
 *
 * @since 1.6035.1445
 */
class Diagnostic_Sla_Monitoring_Dashboard extends Diagnostic_Base {

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
	protected static $description = 'Checks if real-time SLA monitoring dashboard is configured';

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
	 * @since  1.6035.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for popular SLA monitoring plugins.
		$monitoring_plugins = array(
			'jetpack/jetpack.php'                           => 'Jetpack',
			'mainwp/mainwp.php'                             => 'MainWP',
			'managewp-worker/init.php'                      => 'ManageWP',
			'uptime-robot/uptime-robot.php'                 => 'UptimeRobot',
			'statuspage-io/statuspage.php'                  => 'StatusPage.io',
			'wp-site-monitor/wp-site-monitor.php'           => 'WP Site Monitor',
			'application-performance-monitor/apm.php'       => 'Application Performance Monitor',
			'new-relic-reporting/newrelic-reporting.php'    => 'New Relic',
		);

		$has_monitoring = false;
		$active_monitors = array();

		foreach ( $monitoring_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_monitoring = true;
				$active_monitors[] = $plugin_name;
			}
		}

		// Check for custom monitoring options.
		$custom_sla_enabled = get_option( 'wpshadow_sla_monitoring_enabled', false );
		$custom_sla_endpoint = get_option( 'wpshadow_sla_monitoring_endpoint', '' );

		if ( $custom_sla_enabled || ! empty( $custom_sla_endpoint ) ) {
			$has_monitoring = true;
			$active_monitors[] = 'Custom SLA Monitoring';
		}

		// Check for server-level monitoring through constants.
		if ( defined( 'WP_NEWRELIC_APPNAME' ) || 
		     defined( 'DATADOG_ENABLED' ) || 
		     defined( 'SENTRY_DSN' ) ||
		     defined( 'ELASTIC_APM_ENABLED' ) ) {
			$has_monitoring = true;
			$active_monitors[] = 'Server-level APM';
		}

		// Check for external monitoring through health check endpoints.
		$health_check_active = get_option( 'wpshadow_health_endpoint_enabled', false );
		if ( $health_check_active ) {
			$has_monitoring = true;
			$active_monitors[] = 'Health Check Endpoint';
		}

		// Check WordPress Site Health (basic monitoring).
		$site_health_tests = get_transient( 'health-check-site-status-result' );
		$has_wp_health = ! empty( $site_health_tests );

		if ( ! $has_monitoring ) {
			$issues[] = __( 'No SLA monitoring dashboard or service configured', 'wpshadow' );
			$issues[] = __( 'Real-time uptime tracking unavailable', 'wpshadow' );
			$issues[] = __( 'Performance SLA metrics not being collected', 'wpshadow' );
		}

		// Check for uptime requirements defined.
		$sla_target = get_option( 'wpshadow_sla_uptime_target', 0 );
		if ( $has_monitoring && $sla_target < 99.0 ) {
			$issues[] = __( 'No formal SLA target configured (recommended: 99%+ uptime)', 'wpshadow' );
		}

		// Check for alerting integration.
		$alerting_configured = get_option( 'wpshadow_sla_alerting_enabled', false );
		if ( $has_monitoring && ! $alerting_configured ) {
			$issues[] = __( 'SLA monitoring present but alerting not configured', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$description = sprintf(
			/* translators: %s: List of active monitoring tools if any */
			__( 'Enterprise-grade SLA monitoring dashboard is not fully configured. %s', 'wpshadow' ),
			! empty( $active_monitors ) 
				? sprintf( __( 'Currently active: %s', 'wpshadow' ), implode( ', ', $active_monitors ) )
				: __( 'No monitoring tools detected.', 'wpshadow' )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/sla-monitoring-dashboard',
			'issues'       => $issues,
			'persona'      => self::$persona,
			'context'      => array(
				'has_monitoring'      => $has_monitoring,
				'active_monitors'     => $active_monitors,
				'has_wp_health'       => $has_wp_health,
				'sla_target'          => $sla_target,
				'alerting_configured' => $alerting_configured,
			),
		);
	}
}

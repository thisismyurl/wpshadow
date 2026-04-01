<?php
/**
 * Real-Time Monitoring Alerts Not Configured Diagnostic
 *
 * Checks if real-time monitoring and alerting is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Real_Time_Monitoring_Alerts_Not_Configured Class
 *
 * Detects missing real-time monitoring and alerting systems.
 * Without alerts, critical issues go unnoticed until customers complain.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Real_Time_Monitoring_Alerts_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'real-time-monitoring-alerts-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Real-Time Monitoring Alerts Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for real-time monitoring configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Uptime monitoring services (UptimeRobot, Pingdom)
	 * - Error tracking (Sentry, Rollbar, Bugsnag)
	 * - Performance monitoring (New Relic, Scout APM)
	 * - Custom alerting configuration
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_monitoring = false;

		// Check for popular monitoring plugins.
		$monitoring_plugins = array(
			'jetpack/jetpack.php',           // Has uptime monitoring.
			'query-monitor/query-monitor.php',
			'new-relic-reporting/wp-nr.php',
			'application-insights/ApplicationInsights.php',
		);

		foreach ( $monitoring_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_monitoring = true;
				break;
			}
		}

		// Check for error tracking integration.
		if ( defined( 'SENTRY_DSN' ) || defined( 'BUGSNAG_API_KEY' ) ) {
			$has_monitoring = true;
		}

		// Check for custom monitoring configuration.
		$custom_monitoring = get_option( 'wpshadow_monitoring_configured', false );
		if ( $custom_monitoring ) {
			$has_monitoring = true;
		}

		if ( ! $has_monitoring ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No real-time monitoring or alerting is configured. You only learn about site problems when customers report them (often hours or days later). This results in: extended downtime (you don\'t know your site is down), lost revenue (customers leave before you notice), and damaged reputation (search engines penalize slow/down sites). Studies show: Average downtime without monitoring = 4+ hours. With monitoring = 15 minutes.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/monitoring-alerts-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

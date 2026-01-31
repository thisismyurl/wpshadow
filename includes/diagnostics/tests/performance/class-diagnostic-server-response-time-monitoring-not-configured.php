<?php
/**
 * Server Response Time Monitoring Not Configured Diagnostic
 *
 * Checks if server response time is monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server Response Time Monitoring Not Configured Diagnostic Class
 *
 * Detects missing server response time monitoring.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Server_Response_Time_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'server-response-time-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Server Response Time Monitoring Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if server response time is monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for monitoring plugins
		$monitor_plugins = array(
			'upup-remote-monitoring/upup.php',
			'wp-uptime-monitor/wp-uptime-monitor.php',
		);

		$monitor_active = false;
		foreach ( $monitor_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$monitor_active = true;
				break;
			}
		}

		if ( ! $monitor_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Server response time is not monitored. Use uptime monitoring to track performance and get alerts for downtime.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/server-response-time-monitoring-not-configured',
			);
		}

		return null;
	}
}

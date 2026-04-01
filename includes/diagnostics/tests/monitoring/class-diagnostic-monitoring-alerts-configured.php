<?php
/**
 * Monitoring Alerts Configured Diagnostic
 *
 * Validates that uptime alert recipients and thresholds are configured.
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
 * Monitoring Alerts Configured Diagnostic Class
 *
 * Detects missing alert recipients or alert thresholds for uptime monitoring.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Monitoring_Alerts_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'monitoring-alerts-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Monitoring Alerts Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if uptime monitoring alerts are configured and deliverable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$monitoring_plugins = array(
			'jetpack/jetpack.php'                   => 'Jetpack Monitor',
			'uptime-robot-monitor/uptime-robot.php' => 'UptimeRobot',
			'wp-downtime-monitor/wp-downtime.php'   => 'WP Downtime Monitor',
			'managewp-worker/managewp-worker.php'  => 'ManageWP',
		);

		$active_plugin = self::get_first_active_plugin( $monitoring_plugins );
		$custom_monitoring = (bool) get_option( 'wpshadow_uptime_monitoring_enabled', false );

		if ( ! $active_plugin && ! $custom_monitoring ) {
			return null;
		}

		$admin_email = get_option( 'admin_email' );
		$valid_admin_email = ! empty( $admin_email ) && is_email( $admin_email );
		$alert_recipients = get_option( 'wpshadow_monitoring_alert_recipients', array() );
		$alert_threshold = (int) get_option( 'wpshadow_monitoring_alert_threshold_minutes', 0 );

		$has_recipients = $valid_admin_email || ( is_array( $alert_recipients ) && ! empty( $alert_recipients ) );
		$has_thresholds = $alert_threshold > 0 && $alert_threshold <= 10;

		if ( ! $has_recipients || ! $has_thresholds ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Uptime monitoring is active, but alert recipients or thresholds are not configured. Configure alerts to avoid silent outages.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/monitoring-alerts-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'active_plugin'     => $active_plugin,
					'valid_admin_email' => $valid_admin_email,
					'alert_recipients'  => $alert_recipients,
					'alert_threshold'   => $alert_threshold,
				),
			);
		}

		return null;
	}

	/**
	 * Get the first active plugin from a list.
	 *
	 * @since 0.6093.1200
	 * @param  array $plugins Plugin list (file => label).
	 * @return string|null Active plugin label or null.
	 */
	private static function get_first_active_plugin( array $plugins ): ?string {
		foreach ( $plugins as $plugin => $label ) {
			if ( is_plugin_active( $plugin ) ) {
				return $label;
			}
		}

		return null;
	}
}

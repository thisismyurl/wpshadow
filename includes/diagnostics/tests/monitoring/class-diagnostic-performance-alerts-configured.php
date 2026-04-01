<?php
/**
 * Performance Alerts Configured Diagnostic
 *
 * Ensures performance alerting is configured for key thresholds.
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
 * Performance Alerts Configured Diagnostic Class
 *
 * Detects missing performance alerting configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Performance_Alerts_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-alerts-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Alerts Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance alerts are configured for slow pages or poor Web Vitals';

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
		$alerting_plugins = array(
			'jetpack/jetpack.php' => 'Jetpack Downtime Monitoring',
			'newrelic-for-php/newrelic.php' => 'New Relic',
			'managewp-worker/managewp-worker.php' => 'ManageWP',
			'mainwp-child/mainwp-child.php' => 'MainWP',
			'uptimerobot/uptimerobot.php' => 'UptimeRobot',
			'wp-uptime-robot/wp-uptime-robot.php' => 'Uptime Robot',
			'wordfence/wordfence.php' => 'Wordfence',
		);

		$active_plugin = self::get_first_active_plugin( $alerting_plugins );
		$alerts_enabled = (bool) get_option( 'wpshadow_performance_alerts_enabled', false );

		if ( ! $active_plugin && ! $alerts_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Performance alerts are not configured. Set thresholds for slow pages, Web Vitals regressions, or uptime events to catch problems early.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-alerts-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'active_plugin' => $active_plugin,
					'alerts_enabled' => $alerts_enabled,
					'recommendation' => __( 'Enable alerting in your monitoring platform for LCP/INP/CLS regressions and slow endpoints.', 'wpshadow' ),
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

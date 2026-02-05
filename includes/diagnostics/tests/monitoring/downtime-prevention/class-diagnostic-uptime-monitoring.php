<?php
/**
 * Uptime Monitoring Configuration Diagnostic
 *
 * Checks if uptime monitoring service is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1625
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uptime Monitoring Configuration Diagnostic Class
 *
 * Verifies uptime monitoring is actively watching the site.
 * Like having a security guard watching your building 24/7.
 *
 * @since 1.6035.1625
 */
class Diagnostic_Uptime_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uptime-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Uptime Monitoring Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if uptime monitoring service is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Run the uptime monitoring diagnostic check.
	 *
	 * @since  1.6035.1625
	 * @return array|null Finding array if monitoring not configured, null otherwise.
	 */
	public static function check() {
		// Check for uptime monitoring plugins.
		$monitoring_plugins = array(
			'Jetpack Monitor'     => class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) && \Jetpack::is_module_active( 'monitor' ),
			'WP Cron Status Check' => class_exists( 'WP_Cron_Status_Check' ),
			'ManageWP'            => defined( 'MMB_WORKER_VERSION' ),
			'MainWP'              => defined( 'MAINWP_CHILD_VERSION' ),
			'InfiniteWP'          => defined( 'IWP_PLUGIN_DIR' ),
		);

		$active_monitoring = array();
		foreach ( $monitoring_plugins as $name => $detected ) {
			if ( $detected ) {
				$active_monitoring[] = $name;
			}
		}

		// Check for managed hosting with built-in monitoring.
		$managed_hosts = array(
			'WP Engine'   => defined( 'WPE_APIKEY' ),
			'Kinsta'      => defined( 'KINSTAMU_VERSION' ),
			'Flywheel'    => defined( 'FLYWHEEL_CONFIG_DIR' ),
			'Pressable'   => defined( 'IS_PRESSABLE' ),
			'Pagely'      => defined( 'PAGELY_VERSION' ),
		);

		foreach ( $managed_hosts as $host => $detected ) {
			if ( $detected ) {
				$active_monitoring[] = $host . ' Built-in Monitoring';
				break;
			}
		}

		// Check for external monitoring configured (via options).
		$external_services = array(
			'UptimeRobot'  => get_option( 'wpshadow_uptimerobot_configured', false ),
			'Pingdom'      => get_option( 'wpshadow_pingdom_configured', false ),
			'StatusCake'   => get_option( 'wpshadow_statuscake_configured', false ),
			'Site24x7'     => get_option( 'wpshadow_site24x7_configured', false ),
		);

		foreach ( $external_services as $service => $configured ) {
			if ( $configured ) {
				$active_monitoring[] = $service;
			}
		}

		if ( empty( $active_monitoring ) ) {
			return array(
				'id'           => self::$slug . '-not-configured',
				'title'        => __( 'Uptime Monitoring Not Configured', 'wpshadow' ),
				'description'  => __( 'Your site doesn\'t have uptime monitoring set up (like having no security guard watching your store). Without monitoring, you won\'t know if your site goes down unless a visitor tells you—which could be hours or days later. Set up a free service like UptimeRobot or use Jetpack Monitor to get instant alerts when your site becomes unavailable. Most uptime monitoring services are free for basic use.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/uptime-monitoring',
				'context'      => array(),
			);
		}

		// Check if monitoring is recent (hasn't checked in a while).
		$last_check = get_transient( 'wpshadow_last_uptime_check' );
		if ( false !== $last_check ) {
			$hours_since_check = ( time() - $last_check ) / 3600;

			if ( $hours_since_check > 24 ) {
				return array(
					'id'           => self::$slug . '-stale',
					'title'        => __( 'Uptime Monitoring May Be Inactive', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %d: hours since last check */
						__( 'Your uptime monitoring hasn\'t checked your site in %d hours (like a security guard who stopped doing rounds). This suggests your monitoring service may be misconfigured or paused. Verify your monitoring service is active and checking your site regularly (every 1-5 minutes is typical).', 'wpshadow' ),
						(int) $hours_since_check
					),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/uptime-monitoring',
					'context'      => array(
						'hours_since_check' => $hours_since_check,
						'last_check'        => $last_check,
					),
				);
			}
		}

		return null; // Monitoring is configured and active.
	}
}

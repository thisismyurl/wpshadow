<?php
/**
 * Uptime Monitoring Diagnostic
 *
 * Analyzes uptime monitoring configuration and availability tracking.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uptime Monitoring Diagnostic
 *
 * Evaluates uptime monitoring and availability tracking implementation.
 *
 * @since 1.6093.1200
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
	protected static $title = 'Uptime Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes uptime monitoring configuration and availability tracking';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check for uptime monitoring plugins
		$monitoring_plugins = array(
			'jetpack/jetpack.php'                       => 'Jetpack Monitor',
			'uptime-robot-monitor/uptime-robot.php'     => 'UptimeRobot',
			'wordpress-ping/wordpress-ping.php'         => 'WordPress Ping',
			'wp-downtime-monitor/wp-downtime.php'       => 'WP Downtime Monitor',
		);

		$active_plugin = null;
		foreach ( $monitoring_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugin = $name;
				break;
			}
		}

		// Check for external monitoring indicators
		$has_external_monitor = false;

		// Check if site responds to monitoring pings
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		if ( strpos( $user_agent, 'UptimeRobot' ) !== false ||
		     strpos( $user_agent, 'Pingdom' ) !== false ||
		     strpos( $user_agent, 'StatusCake' ) !== false ) {
			$has_external_monitor = true;
		}

		// Check for WP Cron health (indirect uptime indicator)
		$cron_array = _get_cron_array();
		$has_cron_jobs = ! empty( $cron_array );

		$monitoring_interval = (int) get_option( 'wpshadow_uptime_check_interval', 0 );
		$has_frequent_checks = ( $monitoring_interval > 0 && $monitoring_interval <= 5 );

		// Estimate site importance based on activity
		global $wpdb;
		$post_count = wp_count_posts()->publish ?? 0;
		$user_count = count_users();
		$is_production_site = $post_count > 10 || ( $user_count['total_users'] ?? 0 ) > 5;

		// Generate findings if no monitoring on production site
		if ( $is_production_site && ! $active_plugin && ! $has_external_monitor ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No uptime monitoring configured. Production sites should have 24/7 uptime monitoring to detect outages quickly.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/uptime-monitoring',
				'meta'         => array(
					'active_plugin'        => $active_plugin,
					'has_external_monitor' => $has_external_monitor,
					'is_production_site'   => $is_production_site,
					'post_count'           => $post_count,
					'has_cron_jobs'        => $has_cron_jobs,
					'has_frequent_checks'  => $has_frequent_checks,
					'recommendation'       => 'Configure UptimeRobot, Pingdom, or similar service',
					'impact_estimate'      => 'Detect outages within 1-5 minutes vs hours/days',
					'monitoring_services'  => array(
						'UptimeRobot (free for 50 monitors)',
						'Pingdom (paid)',
						'StatusCake (free tier)',
						'Jetpack Monitor (free with Jetpack)',
						'Better Uptime',
					),
					'best_practices'       => array(
						'Check every 1-5 minutes',
						'Monitor from multiple locations',
						'Set up email/SMS alerts',
						'Test critical pages, not just homepage',
						'Monitor SSL certificate expiry',
					),
				),
			);
		}

		return null;
	}
}

<?php
/**
 * Diagnostic: Remote Site Monitoring (Uptime Check)
 *
 * Checks if site is being monitored for uptime and performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Remote_Site_Monitoring
 *
 * Identifies if external uptime monitoring is configured and recommends
 * monitoring services for business-critical sites.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Remote_Site_Monitoring extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'remote-site-monitoring';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Remote Site Monitoring (Uptime Check)';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Check if site is being monitored for uptime and performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Detects common monitoring services and provides recommendations.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if no monitoring, null if detected.
	 */
	public static function check() {
		$monitoring_detected = false;
		$monitoring_service = '';

		// Check for common monitoring plugins
		$monitoring_plugins = array(
			'jetpack/jetpack.php' => 'Jetpack Monitor',
			'uptime-robot/uptime-robot.php' => 'UptimeRobot',
			'site24x7/site24x7.php' => 'Site24x7',
			'pingdom/pingdom.php' => 'Pingdom',
			'wordpress-beta-tester/wp-beta-tester.php' => 'WordPress Beta Tester (includes monitoring)',
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $monitoring_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$monitoring_detected = true;
				$monitoring_service = $plugin_name;
				break;
			}
		}

		// Check if monitoring is configured via transient or option
		if ( ! $monitoring_detected ) {
			$monitoring_data = get_transient( 'wpshadow_monitoring_configured' );
			if ( false !== $monitoring_data && ! empty( $monitoring_data['service'] ) ) {
				$monitoring_detected = true;
				$monitoring_service = sanitize_text_field( $monitoring_data['service'] );
			}
		}

		if ( $monitoring_detected ) {
			// Monitoring is configured - this is good
			return null;
		}

		// No monitoring detected - provide recommendation
		$description = __( 'No uptime monitoring service detected. Without monitoring, you won\'t know when your site goes down until customers report it. Uptime monitoring services (many offer free tiers) automatically check your site every few minutes and alert you immediately if it becomes unavailable. This is critical for business sites where downtime means lost revenue. Popular options include UptimeRobot (free for 50 monitors), Pingdom, Site24x7, StatusCake, and Jetpack Monitor.', 'wpshadow' );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/infrastructure-remote-site-monitoring',
			'meta'        => array(
				'monitoring_detected' => false,
				'recommendation' => 'Set up uptime monitoring for downtime alerts',
			),
		);
	}
}

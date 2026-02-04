<?php
/**
 * Alert System Configuration Diagnostic
 *
 * Checks if downtime alerts are properly configured.
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
 * Alert System Configuration Diagnostic Class
 *
 * Verifies downtime alerts are set up to notify you immediately.
 * Like having a smoke alarm that actually works.
 *
 * @since 1.6035.1625
 */
class Diagnostic_Alert_System extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'alert-system';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Alert System Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if downtime alerts are properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Run the alert system diagnostic check.
	 *
	 * @since  1.6035.1625
	 * @return array|null Finding array if alerts not configured, null otherwise.
	 */
	public static function check() {
		$alert_methods = array();

		// Check for email alerts configured.
		$admin_email = get_option( 'admin_email' );
		if ( ! empty( $admin_email ) ) {
			$alert_methods['email'] = $admin_email;
		}

		// Check for monitoring service alerts.
		$jetpack_monitor = class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) && \Jetpack::is_module_active( 'monitor' );
		if ( $jetpack_monitor ) {
			$alert_methods['jetpack_monitor'] = true;
		}

		// Check for external alert services configured.
		$external_alerts = array(
			'slack'    => get_option( 'wpshadow_slack_webhook_configured', false ),
			'sms'      => get_option( 'wpshadow_sms_alerts_configured', false ),
			'telegram' => get_option( 'wpshadow_telegram_configured', false ),
			'discord'  => get_option( 'wpshadow_discord_webhook_configured', false ),
		);

		foreach ( $external_alerts as $service => $configured ) {
			if ( $configured ) {
				$alert_methods[ $service ] = true;
			}
		}

		// Check if alerts are completely unconfigured.
		if ( empty( $alert_methods ) ) {
			return array(
				'id'           => self::$slug . '-not-configured',
				'title'        => __( 'No Downtime Alerts Configured', 'wpshadow' ),
				'description'  => __( 'You have no way to be notified if your site goes down (like having a smoke alarm with no battery). Without alerts, your site could be offline for hours before you notice. Set up email alerts as a minimum—add your email to Settings → General → Administration Email Address. For better coverage, use a monitoring service like UptimeRobot (free) that can send SMS, Slack, or other instant notifications.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/alert-configuration',
				'context'      => array(),
			);
		}

		// Check if only relying on email (can be unreliable).
		if ( 1 === count( $alert_methods ) && isset( $alert_methods['email'] ) ) {
			return array(
				'id'           => self::$slug . '-email-only',
				'title'        => __( 'Only Email Alerts Configured', 'wpshadow' ),
				'description'  => __( 'Your downtime alerts only go to email (like only having one phone number for emergencies). Email can be delayed, filtered as spam, or missed. Add a second alert method like SMS, Slack, or push notifications for critical issues. Most monitoring services offer multiple notification channels—UptimeRobot, Pingdom, and StatusCake all support SMS and mobile apps.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/alert-configuration',
				'context'      => array(
					'email' => $alert_methods['email'],
				),
			);
		}

		// Check if test alerts have been sent recently.
		$last_test_alert = get_option( 'wpshadow_last_test_alert', 0 );
		$days_since_test = ( time() - $last_test_alert ) / DAY_IN_SECONDS;

		if ( $days_since_test > 90 || 0 === $last_test_alert ) {
			return array(
				'id'           => self::$slug . '-not-tested',
				'title'        => __( 'Alert System Not Tested Recently', 'wpshadow' ),
				'description'  => __( 'You haven\'t tested your downtime alerts in over 90 days (like never checking if your smoke alarm works). Alerts can break due to email changes, phone number updates, expired API keys, or service configuration changes. Test your alerts quarterly to ensure they work when you need them. Most monitoring services have a "test alert" button.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/test-alerts',
				'context'      => array(
					'days_since_test' => $days_since_test,
				),
			);
		}

		return null; // Alerts are properly configured and tested.
	}
}

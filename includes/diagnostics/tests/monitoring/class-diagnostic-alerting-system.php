<?php
/**
 * Alerting System Diagnostic
 *
 * Checks if PagerDuty or equivalent alerting system is configured.
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
 * Alerting System Diagnostic Class
 *
 * Detects if enterprise-grade alerting system is configured
 * for critical incident notifications.
 *
 * @since 1.6035.1445
 */
class Diagnostic_Alerting_System extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'alerting-system';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Alerting System';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PagerDuty or equivalent alerting system is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'incident-management';

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

		// Check for alerting system configuration.
		$alerting_enabled = get_option( 'wpshadow_alerting_enabled', false );
		$alerting_service = get_option( 'wpshadow_alerting_service', '' );

		// Check for PagerDuty integration.
		$pagerduty_key = get_option( 'pagerduty_api_key', '' );
		$pagerduty_integration_key = get_option( 'pagerduty_integration_key', '' );
		$has_pagerduty = ! empty( $pagerduty_key ) || 
		                 ! empty( $pagerduty_integration_key ) ||
		                 defined( 'PAGERDUTY_API_KEY' );

		// Check for popular alerting plugins.
		$alerting_plugins = array(
			'pagerduty-integration/pagerduty.php'     => 'PagerDuty',
			'opsgenie-integration/opsgenie.php'       => 'Opsgenie',
			'slack/slack.php'                         => 'Slack',
			'wp-telegram/wp-telegram.php'             => 'Telegram',
			'pushover-notifications/pushover.php'     => 'Pushover',
			'twilio-notifications/twilio.php'         => 'Twilio SMS',
		);

		$has_alerting_plugin = false;
		$active_alerting_tools = array();

		foreach ( $alerting_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_alerting_plugin = true;
				$active_alerting_tools[] = $plugin_name;
			}
		}

		// Check for monitoring service integrations.
		$monitoring_services = array();
		
		if ( defined( 'NEW_RELIC_APPNAME' ) || defined( 'NEWRELIC_LICENSE_KEY' ) ) {
			$monitoring_services[] = 'New Relic';
		}
		
		if ( defined( 'DATADOG_API_KEY' ) || get_option( 'datadog_api_key', '' ) ) {
			$monitoring_services[] = 'Datadog';
		}
		
		if ( defined( 'SENTRY_DSN' ) || get_option( 'sentry_dsn', '' ) ) {
			$monitoring_services[] = 'Sentry';
		}

		if ( get_option( 'elastic_apm_enabled', false ) ) {
			$monitoring_services[] = 'Elastic APM';
		}

		// Check email alerting (fallback).
		$admin_email = get_option( 'admin_email', '' );
		$alert_emails = get_option( 'wpshadow_alert_emails', array() );
		$has_email_alerts = ! empty( $alert_emails ) || ! empty( $admin_email );

		// Check alert rules configuration.
		$alert_rules = get_option( 'wpshadow_alert_rules', array() );
		$has_alert_rules = is_array( $alert_rules ) && count( $alert_rules ) > 0;

		// Check escalation policy.
		$escalation_policy = get_option( 'wpshadow_escalation_policy', '' );
		$has_escalation = ! empty( $escalation_policy );

		// Check on-call schedule.
		$oncall_schedule = get_option( 'wpshadow_oncall_schedule', array() );
		$has_oncall = is_array( $oncall_schedule ) && count( $oncall_schedule ) > 0;

		// Check recent alerts.
		$last_alert_sent = get_option( 'wpshadow_last_alert_timestamp', 0 );
		$days_since_alert = $last_alert_sent > 0 
			? ( time() - $last_alert_sent ) / DAY_IN_SECONDS 
			: 9999;

		// Check alert acknowledgment tracking.
		$alert_ack_tracking = get_option( 'wpshadow_alert_acknowledgment_enabled', false );

		// Determine if any enterprise alerting is configured.
		$has_enterprise_alerting = $has_pagerduty || 
		                           ! empty( $monitoring_services ) ||
		                           $has_alerting_plugin ||
		                           $alerting_enabled;

		// Evaluate issues.
		if ( ! $has_enterprise_alerting ) {
			$issues[] = __( 'No enterprise alerting system configured', 'wpshadow' );
			$issues[] = __( 'Critical incidents may go unnoticed', 'wpshadow' );
		}

		if ( $has_enterprise_alerting && ! $has_alert_rules ) {
			$issues[] = __( 'Alerting system configured but no alert rules defined', 'wpshadow' );
		}

		if ( $has_enterprise_alerting && ! $has_escalation ) {
			$issues[] = __( 'No escalation policy configured for unacknowledged alerts', 'wpshadow' );
		}

		if ( $has_enterprise_alerting && ! $has_oncall ) {
			$issues[] = __( 'No on-call schedule defined for alert routing', 'wpshadow' );
		}

		if ( $has_enterprise_alerting && ! $alert_ack_tracking ) {
			$issues[] = __( 'Alert acknowledgment tracking not enabled', 'wpshadow' );
		}

		if ( $has_enterprise_alerting && $days_since_alert > 90 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'No alerts sent in %d+ days - alerting may not be working', 'wpshadow' ),
				floor( $days_since_alert )
			);
		}

		if ( $has_pagerduty && empty( $pagerduty_integration_key ) ) {
			$issues[] = __( 'PagerDuty API key found but integration key missing', 'wpshadow' );
		}

		if ( $has_email_alerts && ! $has_enterprise_alerting ) {
			$issues[] = __( 'Only email alerting configured (not suitable for 24/7 response)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$all_tools = array_merge( $active_alerting_tools, $monitoring_services );
		
		$description = sprintf(
			/* translators: %s: list of configured alerting tools */
			__( 'Enterprise alerting system not fully configured. %s', 'wpshadow' ),
			! empty( $all_tools ) 
				? sprintf( __( 'Currently configured: %s', 'wpshadow' ), implode( ', ', $all_tools ) )
				: __( 'No alerting tools detected.', 'wpshadow' )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/alerting-system',
			'issues'       => $issues,
			'persona'      => self::$persona,
			'context'      => array(
				'has_enterprise_alerting' => $has_enterprise_alerting,
				'has_pagerduty'           => $has_pagerduty,
				'active_alerting_tools'   => $active_alerting_tools,
				'monitoring_services'     => $monitoring_services,
				'has_alert_rules'         => $has_alert_rules,
				'has_escalation'          => $has_escalation,
				'has_oncall'              => $has_oncall,
				'days_since_alert'        => floor( $days_since_alert ),
				'alert_ack_tracking'      => $alert_ack_tracking,
				'has_email_alerts'        => $has_email_alerts,
			),
		);
	}
}

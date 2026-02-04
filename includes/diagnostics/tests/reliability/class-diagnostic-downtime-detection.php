<?php
/**
 * Downtime Detection Diagnostic
 *
 * Checks if site uptime is being monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Downtime Detection Diagnostic Class
 *
 * Verifies that site uptime monitoring is active and that
 * downtime alerts are configured.
 *
 * @since 1.6035.1415
 */
class Diagnostic_Downtime_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'downtime-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Downtime Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site uptime is being monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the downtime detection diagnostic check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if downtime detection issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for uptime monitoring plugin.
		$monitoring_plugins = array(
			'uptime-monitor/uptime-monitor.php',
			'heartbeat-by-webdevstudios/heartbeat.php',
		);

		$has_monitoring = false;
		foreach ( $monitoring_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_monitoring = true;
				break;
			}
		}

		$stats['uptime_monitoring_plugin'] = $has_monitoring;

		if ( ! $has_monitoring ) {
			$warnings[] = __( 'No uptime monitoring plugin active', 'wpshadow' );
		}

		// Check for external uptime monitoring service.
		$external_monitoring = get_option( 'wpshadow_external_uptime_monitoring' );
		$stats['external_monitoring'] = ! empty( $external_monitoring ) ? 'Configured' : 'Not configured';

		if ( ! $external_monitoring ) {
			$warnings[] = __( 'No external uptime monitoring service configured', 'wpshadow' );
		}

		// Check for downtime alerts.
		$downtime_alerts = get_option( 'wpshadow_downtime_alerts_enabled' );
		$stats['downtime_alerts'] = boolval( $downtime_alerts );

		if ( ! $downtime_alerts ) {
			$warnings[] = __( 'Downtime alerts not enabled', 'wpshadow' );
		}

		// Check alert email.
		$alert_email = get_option( 'wpshadow_downtime_alert_email' );
		$stats['alert_email'] = ! empty( $alert_email ) ? 'Configured' : 'Not configured';

		if ( ! $alert_email ) {
			$warnings[] = __( 'Downtime alert email not configured', 'wpshadow' );
		}

		// Get current site status.
		$site_response = wp_remote_head( get_home_url(), array(
			'timeout'   => 5,
			'sslverify' => false,
		) );

		$site_online = ! is_wp_error( $site_response );
		$stats['site_currently_online'] = $site_online;

		if ( ! $site_online ) {
			$issues[] = __( 'Site currently offline or unreachable', 'wpshadow' );
		}

		// Check HTTP status code.
		if ( $site_online ) {
			$status_code = wp_remote_retrieve_response_code( $site_response );
			$stats['http_status_code'] = $status_code;

			if ( $status_code !== 200 ) {
				$warnings[] = sprintf(
					/* translators: %d: code */
					__( 'Unexpected HTTP status code: %d (expected 200)', 'wpshadow' ),
					$status_code
				);
			}
		}

		// Check uptime percentage.
		$uptime_percent = get_option( 'wpshadow_uptime_percent_30day' );
		$stats['uptime_30_days'] = ! empty( $uptime_percent ) ? floatval( $uptime_percent ) . '%' : 'Not tracked';

		if ( ! empty( $uptime_percent ) ) {
			if ( floatval( $uptime_percent ) < 99.9 ) {
				$warnings[] = sprintf(
					/* translators: %s: percentage */
					__( '30-day uptime is %s%% (target: 99.9%%)', 'wpshadow' ),
					$uptime_percent
				);
			}
		}

		// Check total downtime incidents.
		$incidents = get_option( 'wpshadow_downtime_incidents_30day', 0 );
		$stats['downtime_incidents_30_days'] = intval( $incidents );

		if ( $incidents > 2 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d downtime incidents in last 30 days', 'wpshadow' ),
				$incidents
			);
		}

		// Check average incident duration.
		$avg_incident_duration = get_option( 'wpshadow_avg_incident_duration_minutes' );
		$stats['avg_incident_duration'] = ! empty( $avg_incident_duration ) ? intval( $avg_incident_duration ) . ' minutes' : 'Not tracked';

		if ( ! empty( $avg_incident_duration ) && intval( $avg_incident_duration ) > 30 ) {
			$warnings[] = sprintf(
				/* translators: %d: minutes */
				__( 'Average incident duration is %d minutes - investigate causes', 'wpshadow' ),
				intval( $avg_incident_duration )
			);
		}

		// Check for status page.
		$status_page_enabled = get_option( 'wpshadow_status_page_enabled' );
		$stats['status_page'] = boolval( $status_page_enabled );

		if ( ! $status_page_enabled ) {
			$warnings[] = __( 'Public status page not enabled - customers can\'t see incident status', 'wpshadow' );
		}

		// Check heartbeat monitoring.
		$heartbeat_enabled = get_option( 'wpshadow_heartbeat_monitoring' );
		$stats['heartbeat_monitoring'] = boolval( $heartbeat_enabled );

		if ( ! $heartbeat_enabled ) {
			$warnings[] = __( 'Heartbeat monitoring not enabled', 'wpshadow' );
		}

		// Check monitoring frequency.
		$check_frequency = get_option( 'wpshadow_uptime_check_frequency', 300 );
		$stats['check_frequency_seconds'] = intval( $check_frequency );

		if ( $check_frequency > 600 ) { // 10 minutes.
			$warnings[] = sprintf(
				/* translators: %d: seconds */
				__( 'Uptime check frequency is every %d seconds - may be too infrequent', 'wpshadow' ),
				$check_frequency
			);
		}

		// Check for escalation policy.
		$escalation_policy = get_option( 'wpshadow_escalation_policy' );
		$stats['escalation_policy'] = boolval( $escalation_policy );

		if ( ! $escalation_policy ) {
			$warnings[] = __( 'No escalation policy configured for downtime events', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Downtime detection has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/downtime-detection',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Downtime detection has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/downtime-detection',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Downtime detection is active.
	}
}

<?php
/**
 * Security Monitoring Diagnostic
 *
 * Checks if real-time security event monitoring is active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Monitoring Diagnostic Class
 *
 * Verifies that real-time security event monitoring is active to
 * detect and respond to security threats immediately.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Security_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if real-time security event monitoring is active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the security monitoring diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if security monitoring issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for security monitoring plugin.
		$security_plugins = array(
			'wordfence-security/wordfence.php',
			'all-in-one-wp-security-and-firewall/all_in_one_wp_security_and_firewall.php',
			'sucuri-scanner/sucuri.php',
			'ithemes-security-pro/iThemesSecurity.php',
		);

		$has_security_plugin = false;
		$active_plugin = null;

		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_security_plugin = true;
				$active_plugin = $plugin;
				break;
			}
		}

		$stats['security_plugin'] = $active_plugin ?: 'None';

		if ( ! $has_security_plugin ) {
			$issues[] = __( 'No security monitoring plugin active', 'wpshadow' );
		}

		// Check for real-time alerts configuration.
		$alerts_enabled = get_option( 'wpshadow_realtime_alerts_enabled' );
		$stats['realtime_alerts_enabled'] = boolval( $alerts_enabled );

		if ( ! $alerts_enabled ) {
			$warnings[] = __( 'Real-time alerts not enabled', 'wpshadow' );
		}

		// Check for email notifications for security events.
		$security_email = get_option( 'wpshadow_security_alert_email' );
		$stats['security_alert_email'] = ! empty( $security_email ) ? 'Configured' : 'Not configured';

		if ( empty( $security_email ) ) {
			$warnings[] = __( 'Security alert email not configured', 'wpshadow' );
		}

		// Check for login monitoring.
		$login_monitoring = get_option( 'wpshadow_monitor_login_attempts' );
		$stats['login_monitoring'] = boolval( $login_monitoring );

		if ( ! $login_monitoring ) {
			$warnings[] = __( 'Login attempt monitoring not enabled', 'wpshadow' );
		}

		// Check for failed login threshold.
		$max_failed_logins = get_option( 'wpshadow_max_failed_login_attempts', 5 );
		$stats['max_failed_logins'] = intval( $max_failed_logins );

		if ( $max_failed_logins > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'Allowing %d failed login attempts before lockout - too permissive', 'wpshadow' ),
				$max_failed_logins
			);
		}

		// Check for file change detection.
		$file_monitoring = get_option( 'wpshadow_monitor_file_changes' );
		$stats['file_change_monitoring'] = boolval( $file_monitoring );

		if ( ! $file_monitoring ) {
			$warnings[] = __( 'File change monitoring not enabled', 'wpshadow' );
		}

		// Check for malware scanning.
		$malware_scanning = get_option( 'wpshadow_malware_scanning_enabled' );
		$stats['malware_scanning'] = boolval( $malware_scanning );

		if ( ! $malware_scanning ) {
			$warnings[] = __( 'Malware scanning not enabled', 'wpshadow' );
		}

		// Check malware scan frequency.
		$scan_frequency = get_option( 'wpshadow_malware_scan_frequency' );
		$stats['malware_scan_frequency'] = $scan_frequency ?: 'Not scheduled';

		if ( ! $scan_frequency ) {
			$warnings[] = __( 'Malware scan not scheduled', 'wpshadow' );
		}

		// Check for vulnerability monitoring.
		$vuln_monitoring = get_option( 'wpshadow_vulnerability_monitoring' );
		$stats['vulnerability_monitoring'] = boolval( $vuln_monitoring );

		if ( ! $vuln_monitoring ) {
			$warnings[] = __( 'Vulnerability monitoring not enabled', 'wpshadow' );
		}

		// Check for database monitoring.
		$db_monitoring = get_option( 'wpshadow_monitor_database' );
		$stats['database_monitoring'] = boolval( $db_monitoring );

		if ( ! $db_monitoring ) {
			$warnings[] = __( 'Database monitoring not enabled', 'wpshadow' );
		}

		// Check for API monitoring.
		$api_monitoring = get_option( 'wpshadow_monitor_api' );
		$stats['api_monitoring'] = boolval( $api_monitoring );

		if ( ! $api_monitoring ) {
			$warnings[] = __( 'API monitoring not enabled', 'wpshadow' );
		}

		// Check security event log.
		$event_log_enabled = get_option( 'wpshadow_security_event_logging' );
		$stats['event_logging'] = boolval( $event_log_enabled );

		if ( ! $event_log_enabled ) {
			$warnings[] = __( 'Security event logging not enabled', 'wpshadow' );
		}

		// Check event log retention.
		$log_retention = get_option( 'wpshadow_security_log_retention_days', 30 );
		$stats['log_retention_days'] = intval( $log_retention );

		if ( $log_retention < 30 ) {
			$warnings[] = sprintf(
				/* translators: %d: days */
				__( 'Security log retention is %d days (recommended: 30+)', 'wpshadow' ),
				$log_retention
			);
		}

		// Check for incident response plan.
		$incident_plan = get_option( 'wpshadow_incident_response_plan' );
		$stats['incident_response_plan'] = boolval( $incident_plan );

		if ( ! $incident_plan ) {
			$warnings[] = __( 'Incident response plan not documented', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Security monitoring has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-monitoring',
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
				'description'  => __( 'Security monitoring has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-monitoring',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Security monitoring is active.
	}
}

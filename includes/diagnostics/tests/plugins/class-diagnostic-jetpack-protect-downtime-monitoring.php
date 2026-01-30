<?php
/**
 * Jetpack Protect Downtime Monitoring Diagnostic
 *
 * Jetpack Protect Downtime Monitoring misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.875.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Protect Downtime Monitoring Diagnostic Class
 *
 * @since 1.875.0000
 */
class Diagnostic_JetpackProtectDowntimeMonitoring extends Diagnostic_Base {

	protected static $slug = 'jetpack-protect-downtime-monitoring';
	protected static $title = 'Jetpack Protect Downtime Monitoring';
	protected static $description = 'Jetpack Protect Downtime Monitoring misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Downtime monitoring
		$monitoring_enabled = \Jetpack::is_module_active( 'monitor' );
		if ( ! $monitoring_enabled ) {
			$issues[] = __( 'Downtime monitoring disabled (no alerts)', 'wpshadow' );
		}
		
		// Check 2: Notification email
		$notification_email = get_option( 'jetpack_monitor_email', '' );
		if ( empty( $notification_email ) ) {
			$issues[] = __( 'No notification email (alerts not sent)', 'wpshadow' );
		}
		
		// Check 3: Check frequency
		$check_interval = get_option( 'jetpack_monitor_check_interval', 5 );
		if ( $check_interval > 10 ) {
			$issues[] = sprintf( __( 'Long check interval (%d min, delayed detection)', 'wpshadow' ), $check_interval );
		}
		
		// Check 4: Protect module
		$protect_enabled = \Jetpack::is_module_active( 'protect' );
		if ( ! $protect_enabled ) {
			$issues[] = __( 'Protect module disabled (no brute force protection)', 'wpshadow' );
		}
		
		// Check 5: Security scanning
		$scan_enabled = \Jetpack::is_module_active( 'scan' );
		if ( ! $scan_enabled ) {
			$issues[] = __( 'Scan module disabled (no malware detection)', 'wpshadow' );
		}
		
		// Check 6: Backup module
		$backup_enabled = \Jetpack::is_module_active( 'backups' );
		if ( ! $backup_enabled ) {
			$issues[] = __( 'Backups disabled (data loss risk)', 'wpshadow' );
		}
		
		// Check 7: Activity log
		$activity_log = get_option( 'jetpack_activity_log', 0 );
		if ( ! $activity_log ) {
			$issues[] = __( 'Activity log disabled (no audit trail)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Jetpack Protect has %d monitoring issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/jetpack-protect-downtime-monitoring',
		);
	}
}

<?php
/**
 * Sucuri Scan Scheduling Diagnostic
 *
 * Sucuri Scan Scheduling misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.851.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sucuri Scan Scheduling Diagnostic Class
 *
 * @since 1.851.0000
 */
class Diagnostic_SucuriScanScheduling extends Diagnostic_Base {

	protected static $slug = 'sucuri-scan-scheduling';
	protected static $title = 'Sucuri Scan Scheduling';
	protected static $description = 'Sucuri Scan Scheduling misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SUCURISCAN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Scheduled scans enabled
		$schedule = get_option( 'sucuri_scan_schedule_enabled', 0 );
		if ( ! $schedule ) {
			$issues[] = 'Scheduled scans not enabled';
		}
		
		// Check 2: Scan frequency
		$frequency = get_option( 'sucuri_scan_frequency', '' );
		if ( empty( $frequency ) ) {
			$issues[] = 'Scan frequency not configured';
		}
		
		// Check 3: Email alerts
		$alerts = get_option( 'sucuri_scan_email_alerts_enabled', 0 );
		if ( ! $alerts ) {
			$issues[] = 'Email alerts not configured';
		}
		
		// Check 4: Malware scanning
		$malware = get_option( 'sucuri_malware_scan_enabled', 0 );
		if ( ! $malware ) {
			$issues[] = 'Malware scanning not enabled';
		}
		
		// Check 5: File integrity monitoring
		$integrity = get_option( 'sucuri_file_integrity_monitoring_enabled', 0 );
		if ( ! $integrity ) {
			$issues[] = 'File integrity monitoring not enabled';
		}
		
		// Check 6: Backup scans
		$backup = get_option( 'sucuri_scan_backups_enabled', 0 );
		if ( ! $backup ) {
			$issues[] = 'Scan backups not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d scan scheduling issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/sucuri-scan-scheduling',
			);
		}
		
		return null;
	}
}

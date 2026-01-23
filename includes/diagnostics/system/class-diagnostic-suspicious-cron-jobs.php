<?php
declare(strict_types=1);
/**
 * Suspicious Cron Jobs Diagnostic
 *
 * Philosophy: Task security - detect malicious scheduled tasks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for suspicious cron jobs.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Suspicious_Cron_Jobs extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Get all scheduled events
		$crons = _get_cron_array();
		
		if ( empty( $crons ) ) {
			return null;
		}
		
		$suspicious = array();
		
		foreach ( $crons as $timestamp => $cron ) {
			foreach ( $cron as $hook => $details ) {
				// Check for suspicious hook names
				if ( preg_match( '/eval|exec|system|base64|crack|shell|malware|exploit/i', $hook ) ) {
					$suspicious[] = $hook;
				}
			}
		}
		
		if ( ! empty( $suspicious ) ) {
			return array(
				'id'          => 'suspicious-cron-jobs',
				'title'       => 'Suspicious Scheduled Tasks (Crons) Found',
				'description' => sprintf(
					'Malicious cron jobs detected: %s. These are scheduled tasks that run periodically, likely for spreading malware or maintaining access. Remove immediately.',
					implode( ', ', array_slice( $suspicious, 0, 3 ) )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/remove-malicious-crons/',
				'training_link' => 'https://wpshadow.com/training/cron-security/',
				'auto_fixable' => false,
				'threat_level' => 90,
			);
		}
		
		return null;
	}

}
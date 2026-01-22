<?php
declare(strict_types=1);
/**
 * Logging and Monitoring System Diagnostic
 *
 * Philosophy: Incident response - complete audit trails
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if logging and monitoring is active.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Logging_Monitoring extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$logging_plugins = array(
			'wordfence/wordfence.php',
			'activity-log-manager/activity-log-manager.php',
			'stream/stream.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $logging_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'logging-monitoring',
			'title'       => 'No Comprehensive Logging & Monitoring',
			'description' => 'Logins, file changes, database modifications, and security events are not logged. Without audit trails, you cannot detect or investigate breaches. Enable comprehensive logging.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-activity-logging/',
			'training_link' => 'https://wpshadow.com/training/logging-setup/',
			'auto_fixable' => false,
			'threat_level' => 80,
		);
	}
}

<?php
declare(strict_types=1);
/**
 * Real-Time Alert System Diagnostic
 *
 * Philosophy: Incident response - immediate threat notification
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if real-time alerts are configured.
 */
class Diagnostic_Realtime_Alerts extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$alert_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
		);

		$active = get_option( 'active_plugins', array() );
		foreach ( $alert_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}

		return array(
			'id'            => 'realtime-alerts',
			'title'         => 'No Real-Time Security Alerts',
			'description'   => 'Security events (login attempts, file changes, malware) are not sent as real-time alerts. Delays in detection allow attacks to progress. Enable email/SMS alerts for critical events.',
			'severity'      => 'medium',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/configure-security-alerts/',
			'training_link' => 'https://wpshadow.com/training/incident-notification/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
		);
	}
}

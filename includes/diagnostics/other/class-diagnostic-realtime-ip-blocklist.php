<?php
declare(strict_types=1);
/**
 * Real-Time IP Blocklist Diagnostic
 *
 * Philosophy: Threat intelligence - block known attackers
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if real-time IP blocking is active.
 */
class Diagnostic_Realtime_IP_Blocklist extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$ip_block_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
		);

		$active = get_option( 'active_plugins', array() );
		foreach ( $ip_block_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // IP blocking active
			}
		}

		return array(
			'id'            => 'realtime-ip-blocklist',
			'title'         => 'No Real-Time IP Blocking',
			'description'   => 'Your site lacks real-time IP blocking. Malicious IPs continue to attack. Enable IP reputation blocking via security plugin.',
			'severity'      => 'medium',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/enable-ip-blocking/',
			'training_link' => 'https://wpshadow.com/training/ip-blocking/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
		);
	}
}

<?php
declare(strict_types=1);
/**
 * Geofencing/Country Blocking Diagnostic
 *
 * Philosophy: Geographic security - block unwanted regions
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if country-based blocking is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Geofencing extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$geo_plugins = array(
			'geoip-detect/geoip-detect.php',
			'geo-blocker/geo-blocker.php',
		);

		$active = get_option( 'active_plugins', array() );
		foreach ( $geo_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}

		return array(
			'id'            => 'geofencing',
			'title'         => 'No Geographic Access Control',
			'description'   => 'Geographic blocking not configured. Block traffic from countries where you don\'t operate to reduce attack surface.',
			'severity'      => 'low',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/geographic-blocking/',
			'training_link' => 'https://wpshadow.com/training/geofencing/',
			'auto_fixable'  => false,
			'threat_level'  => 50,
		);
	}
}

<?php declare(strict_types=1);
/**
 * Geofencing/Country Blocking Diagnostic
 *
 * Philosophy: Geographic security - block unwanted regions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if country-based blocking is configured.
 */
class Diagnostic_Geofencing {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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
			'id'          => 'geofencing',
			'title'       => 'No Geographic Access Control',
			'description' => 'Geographic blocking not configured. Block traffic from countries where you don\'t operate to reduce attack surface.',
			'severity'    => 'low',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/geographic-blocking/',
			'training_link' => 'https://wpshadow.com/training/geofencing/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}

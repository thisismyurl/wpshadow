<?php
declare(strict_types=1);
/**
 * Resource Hints Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing resource hints on primary domains.
 */
class Diagnostic_Resource_Hints extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$hosts = self::get_hosts();
		if ( empty( $hosts ) ) {
			return null;
		}
		
		return array(
			'id'           => 'resource-hints-missing',
			'title'        => 'Add Resource Hints',
			'description'  => 'Preconnect/preload common domains (CDN, fonts, APIs) to improve first-byte time.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/add-resource-hints/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=resource-hints',
			'auto_fixable' => true,
			'threat_level' => 35,
		);
	}
	
	/**
	 * Collect candidate hosts for hints.
	 *
	 * @return array
	 */
	private static function get_hosts() {
		$hosts = array();
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $site_host ) {
			$hosts[] = $site_host;
		}
		
		$cdn = get_option( 'wpshadow_cdn_host' );
		if ( $cdn ) {
			$hosts[] = $cdn;
		}
		
		return array_unique( array_filter( $hosts ) );
	}
}

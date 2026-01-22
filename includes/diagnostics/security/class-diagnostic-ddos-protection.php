<?php
declare(strict_types=1);
/**
 * DDoS Protection Diagnostic
 *
 * Philosophy: Availability - protection against denial of service
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if DDoS protection is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DDOS_Protection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$ddos_services = array(
			'cloudflare',
			'akamai',
			'sucuri',
		);
		
		foreach ( $ddos_services as $service ) {
			$enabled = get_option( "wpshadow_{$service}_ddos_protection" );
			if ( ! empty( $enabled ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'ddos-protection',
			'title'       => 'No DDoS Protection',
			'description' => 'No DDoS mitigation in place. Large traffic floods can take down your site. Enable DDoS protection via Cloudflare, Sucuri, or similar service.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-ddos-protection/',
			'training_link' => 'https://wpshadow.com/training/ddos-mitigation/',
			'auto_fixable' => false,
			'threat_level' => 75,
		);
	}
}

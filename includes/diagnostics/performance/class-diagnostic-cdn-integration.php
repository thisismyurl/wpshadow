<?php
declare(strict_types=1);
/**
 * Content Delivery Network (CDN) Integration Diagnostic
 *
 * Philosophy: Performance/Security - DDoS mitigation via CDN
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if CDN is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_CDN_Integration extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$cdn_services = array(
			'cloudflare',
			'amazon-cloudfront',
			'keycdn',
			'bunnycdn',
		);
		
		foreach ( $cdn_services as $service ) {
			$enabled = get_option( "wpshadow_{$service}_enabled" );
			if ( ! empty( $enabled ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'cdn-integration',
			'title'       => 'No Content Delivery Network (CDN)',
			'description' => 'CDN not configured. Without CDN, all traffic goes directly to origin server. CDN provides DDoS protection, faster content delivery, and reduced bandwidth.',
			'severity'    => 'low',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/configure-cdn/',
			'training_link' => 'https://wpshadow.com/training/cdn-setup/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}

}
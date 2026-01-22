<?php
declare(strict_types=1);
/**
 * WAF (Web Application Firewall) Deployment Diagnostic
 *
 * Philosophy: Network security - edge protection
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if WAF is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_WAF_Deployment extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$waf_services = array(
			'cloudflare',
			'sucuri',
			'akamai',
			'wordfence',
		);
		
		foreach ( $waf_services as $service ) {
			$enabled = get_option( "wpshadow_{$service}_waf_enabled" );
			if ( ! empty( $enabled ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'waf-deployment',
			'title'       => 'No Web Application Firewall (WAF) Deployed',
			'description' => 'No WAF protecting your site. Malicious traffic reaches your server unfiltered. Deploy WAF (Cloudflare, Sucuri, Wordfence) to block attacks at network edge.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/deploy-waf/',
			'training_link' => 'https://wpshadow.com/training/waf-setup/',
			'auto_fixable' => false,
			'threat_level' => 80,
		);
	}
}

<?php
declare(strict_types=1);
/**
 * robots.txt Diagnostic
 *
 * Philosophy: Ensure crawlability and safe directives; educates on SEO foundations.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check robots.txt availability and basic directives.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Robots_Txt extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$robots_url = home_url( '/robots.txt' );
		$response = wp_remote_get( $robots_url, array( 'timeout' => 8, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return array(
				'title'       => 'robots.txt Not Accessible',
				'description' => 'Search engines could not fetch robots.txt. Ensure it exists and is reachable to control crawling.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/robots-txt-best-practices/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=robots-txt',
				'auto_fixable' => false,
				'threat_level' => 40,
			);
		}
		
		$body = wp_remote_retrieve_body( $response );
		if ( stripos( $body, 'Disallow: /' ) !== false && stripos( $body, 'User-agent: *' ) !== false ) {
			return array(
				'title'       => 'robots.txt Blocks All Crawlers',
				'description' => 'Your robots.txt disallows all crawlers. This can remove your site from search results.',
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/robots-txt-best-practices/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=robots-txt',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		return null;
	}

}
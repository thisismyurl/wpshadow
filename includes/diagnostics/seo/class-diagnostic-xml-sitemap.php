<?php
declare(strict_types=1);
/**
 * XML Sitemap Availability Diagnostic
 *
 * Philosophy: SEO basics to build trust; guides to Pro/Guardian SEO insights.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if XML sitemap is accessible.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_XML_Sitemap extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$sitemap_url = home_url( '/sitemap.xml' );
		$response = wp_remote_head( $sitemap_url, array( 'timeout' => 8, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) >= 400 ) {
			return array(
				'id'          => 'xml-sitemap',
				'title'       => 'XML Sitemap Not Found',
				'description' => 'Search engines rely on your XML sitemap to discover content. Ensure /sitemap.xml is available or provided by your SEO plugin.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/create-xml-sitemap/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=xml-sitemap',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}

}
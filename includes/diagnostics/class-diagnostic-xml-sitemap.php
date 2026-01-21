<?php declare(strict_types=1);
/**
 * XML Sitemap Availability Diagnostic
 *
 * Philosophy: SEO basics to build trust; guides to Pro/Guardian SEO insights.
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if XML sitemap is accessible.
 */
class Diagnostic_XML_Sitemap {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$sitemap_url = home_url( '/sitemap.xml' );
		$response = wp_remote_head( $sitemap_url, array( 'timeout' => 8, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) >= 400 ) {
			return array(
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

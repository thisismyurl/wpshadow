<?php declare(strict_types=1);
/**
 * Missing XML Sitemap Diagnostic
 *
 * Philosophy: SEO indexation - sitemaps help discovery
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for missing XML sitemap.
 */
class Diagnostic_SEO_Missing_XML_Sitemap {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$sitemap_url = home_url( '/sitemap.xml' );
		$response = wp_remote_get( $sitemap_url, array( 'timeout' => 5 ) );
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			// Check for wp-sitemap.xml (WordPress core)
			$wp_sitemap = home_url( '/wp-sitemap.xml' );
			$wp_response = wp_remote_get( $wp_sitemap, array( 'timeout' => 5 ) );
			
			if ( is_wp_error( $wp_response ) || wp_remote_retrieve_response_code( $wp_response ) !== 200 ) {
				return array(
					'id'          => 'seo-missing-xml-sitemap',
					'title'       => 'Missing XML Sitemap',
					'description' => 'No XML sitemap detected. Sitemaps help search engines discover and index your content. Enable WordPress core sitemaps or use an SEO plugin.',
					'severity'    => 'high',
					'category'    => 'seo',
					'kb_link'     => 'https://wpshadow.com/kb/create-xml-sitemap/',
					'training_link' => 'https://wpshadow.com/training/sitemap-optimization/',
					'auto_fixable' => false,
					'threat_level' => 70,
				);
			}
		}
		
		return null;
	}
}

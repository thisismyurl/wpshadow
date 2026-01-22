<?php
declare(strict_types=1);
/**
 * Missing Robots.txt Diagnostic
 *
 * Philosophy: SEO crawlability - robots.txt guides search engines
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing or misconfigured robots.txt.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Robots_Txt extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$robots_url = home_url( '/robots.txt' );
		$response = wp_remote_get( $robots_url, array( 'timeout' => 5 ) );
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return array(
				'id'          => 'seo-missing-robots-txt',
				'title'       => 'Missing or Inaccessible robots.txt',
				'description' => 'robots.txt file missing or not accessible. This file tells search engines which pages to crawl. Create a robots.txt file at site root.',
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/create-robots-txt/',
				'training_link' => 'https://wpshadow.com/training/robots-txt-guide/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}

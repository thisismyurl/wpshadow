<?php
declare(strict_types=1);
/**
 * URL Parameter Handling Diagnostic
 *
 * Philosophy: SEO crawl budget - manage parameters to avoid waste
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for URL parameter issues.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_URL_Parameter_Issues extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$param_urls = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE guid LIKE '%?%' 
			AND post_status = 'publish'"
		);
		
		if ( $param_urls > 0 ) {
			return array(
				'id'          => 'seo-url-parameter-issues',
				'title'       => 'URL Parameters Detected',
				'description' => sprintf( '%d URLs contain parameters (?utm, ?ref, etc). Configure URL parameter handling in Search Console to avoid duplicate content issues. Use canonical tags.', $param_urls ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/handle-url-parameters/',
				'training_link' => 'https://wpshadow.com/training/parameter-handling/',
				'auto_fixable' => false,
				'threat_level' => 50,
			);
		}
		
		return null;
	}

}
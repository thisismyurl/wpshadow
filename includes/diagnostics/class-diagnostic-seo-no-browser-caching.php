<?php declare(strict_types=1);
/**
 * No Browser Caching Diagnostic
 *
 * Philosophy: SEO performance - browser caching speeds repeat visits
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if browser caching is enabled.
 */
class Diagnostic_SEO_No_Browser_Caching {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			$cache_control = $headers['cache-control'] ?? '';
			
			if ( empty( $cache_control ) || strpos( $cache_control, 'no-cache' ) !== false ) {
				return array(
					'id'          => 'seo-no-browser-caching',
					'title'       => 'Browser Caching Not Enabled',
					'description' => 'No cache-control headers detected. Browser caching stores static files locally, speeding up repeat visits. Add cache headers via .htaccess or plugin.',
					'severity'    => 'medium',
					'category'    => 'seo',
					'kb_link'     => 'https://wpshadow.com/kb/enable-browser-caching/',
					'training_link' => 'https://wpshadow.com/training/caching-strategy/',
					'auto_fixable' => false,
					'threat_level' => 60,
				);
			}
		}
		
		return null;
	}
}

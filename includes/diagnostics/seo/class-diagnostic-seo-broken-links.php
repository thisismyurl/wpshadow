<?php
declare(strict_types=1);
/**
 * Broken Links Diagnostic
 *
 * Philosophy: SEO user experience - broken links hurt rankings
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for broken internal/external links.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Broken_Links extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 5"
		);
		
		$broken = 0;
		foreach ( $posts as $post ) {
			preg_match_all( '/<a[^>]*href=["\']([^"\']*)["\'][^>]*>/i', $post->post_content, $matches );
			
			foreach ( $matches[1] as $url ) {
				if ( strpos( $url, 'http' ) === 0 ) {
					$response = wp_remote_head( $url, array( 'timeout' => 5 ) );
					if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) >= 400 ) {
						$broken++;
					}
				}
				
				if ( $broken >= 3 ) {
					break 2;
				}
			}
		}
		
		if ( $broken > 0 ) {
			return array(
				'id'          => 'seo-broken-links',
				'title'       => 'Broken Links Detected',
				'description' => sprintf( 'Found %d broken links (404 errors). Broken links hurt SEO and user experience. Fix or remove broken links.', $broken ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-broken-links/',
				'training_link' => 'https://wpshadow.com/training/link-maintenance/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}

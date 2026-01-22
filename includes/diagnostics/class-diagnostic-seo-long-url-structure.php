<?php declare(strict_types=1);
/**
 * Long URL Structure Diagnostic
 *
 * Philosophy: SEO usability - short URLs are more shareable
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for overly long URLs.
 */
class Diagnostic_SEO_Long_URL_Structure {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_name FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 20"
		);
		
		$long_urls = 0;
		foreach ( $posts as $post ) {
			$permalink = get_permalink( $post->ID );
			if ( strlen( $permalink ) > 75 ) {
				$long_urls++;
			}
		}
		
		if ( $long_urls > 5 ) {
			return array(
				'id'          => 'seo-long-url-structure',
				'title'       => 'URLs Too Long',
				'description' => sprintf( '%d URLs exceed 75 characters. Shorter URLs (50-60 chars) are easier to share, remember, and rank better. Simplify URL structure.', $long_urls ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/shorten-urls/',
				'training_link' => 'https://wpshadow.com/training/url-optimization/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}

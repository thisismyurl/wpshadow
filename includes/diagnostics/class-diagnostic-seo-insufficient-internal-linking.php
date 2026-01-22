<?php declare(strict_types=1);
/**
 * Insufficient Internal Linking Diagnostic
 *
 * Philosophy: SEO authority - internal links distribute page rank
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for insufficient internal linking.
 */
class Diagnostic_SEO_Insufficient_Internal_Linking {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 10"
		);
		
		$issues = 0;
		foreach ( $posts as $post ) {
			$internal_links = preg_match_all( '/<a[^>]*href=["\']https?:\/\/' . preg_quote( home_url(), '/' ) . '/i', $post->post_content );
			if ( $internal_links < 3 ) {
				$issues++;
			}
		}
		
		if ( $issues > 0 ) {
			return array(
				'id'          => 'seo-insufficient-internal-linking',
				'title'       => 'Insufficient Internal Linking',
				'description' => sprintf( '%d posts have fewer than 3 internal links. Internal links help search engines crawl and distribute authority. Add 3-5 relevant internal links per page.', $issues ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/improve-internal-linking/',
				'training_link' => 'https://wpshadow.com/training/internal-link-strategy/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}

<?php
declare(strict_types=1);
/**
 * Thin Content Diagnostic
 *
 * Philosophy: SEO content depth - search engines favor substantial content
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for thin content (low word count).
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Thin_Content extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_title, post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 20"
		);
		
		$thin = 0;
		foreach ( $posts as $post ) {
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
			if ( $word_count < 300 ) {
				$thin++;
			}
		}
		
		if ( $thin > 0 ) {
			return array(
				'id'          => 'seo-thin-content',
				'title'       => 'Thin Content Detected',
				'description' => sprintf( '%d pages have less than 300 words. Search engines favor comprehensive content (800+ words). Expand thin pages with valuable information.', $thin ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-thin-content/',
				'training_link' => 'https://wpshadow.com/training/content-depth/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}

}
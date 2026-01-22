<?php declare(strict_types=1);
/**
 * No Keyword in URL Diagnostic
 *
 * Philosophy: SEO basics - keyword-rich URLs help rankings
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if target keyword is in URL slug.
 */
class Diagnostic_SEO_No_Keyword_In_URL {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_title, post_name FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type = 'post' 
			LIMIT 20"
		);
		
		$issues = 0;
		foreach ( $posts as $post ) {
			$title_words = explode( ' ', strtolower( $post->post_title ) );
			$slug = strtolower( $post->post_name );
			
			$found = false;
			foreach ( $title_words as $word ) {
				if ( strlen( $word ) > 4 && strpos( $slug, $word ) !== false ) {
					$found = true;
					break;
				}
			}
			
			if ( ! $found ) {
				$issues++;
			}
		}
		
		if ( $issues > 5 ) {
			return array(
				'id'          => 'seo-no-keyword-in-url',
				'title'       => 'Keywords Not in URLs',
				'description' => sprintf( '%d posts have URLs without target keywords. Use descriptive, keyword-rich slugs. Avoid dates and generic IDs in URLs.', $issues ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-url-structure/',
				'training_link' => 'https://wpshadow.com/training/seo-friendly-urls/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}

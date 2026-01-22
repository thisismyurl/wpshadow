<?php declare(strict_types=1);
/**
 * Low Keyword Density Diagnostic
 *
 * Philosophy: SEO relevance - target keyword should appear naturally
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for low target keyword density.
 */
class Diagnostic_SEO_Low_Keyword_Density {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_title, post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type = 'post' 
			LIMIT 10"
		);
		
		$issues = 0;
		foreach ( $posts as $post ) {
			// Extract likely target keyword from title
			$title_words = explode( ' ', strtolower( $post->post_title ) );
			$content_lower = strtolower( wp_strip_all_tags( $post->post_content ) );
			
			foreach ( $title_words as $word ) {
				if ( strlen( $word ) > 5 ) {
					$occurrences = substr_count( $content_lower, $word );
					$total_words = str_word_count( $content_lower );
					if ( $total_words > 0 && ( $occurrences / $total_words ) < 0.005 ) {
						$issues++;
						break;
					}
				}
			}
		}
		
		if ( $issues > 3 ) {
			return array(
				'id'          => 'seo-low-keyword-density',
				'title'       => 'Low Keyword Density',
				'description' => sprintf( '%d posts have low keyword density (< 0.5%%). Target keyword should appear naturally 5-10 times per 1000 words. Use variations and synonyms.', $issues ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-keyword-density/',
				'training_link' => 'https://wpshadow.com/training/keyword-usage/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}

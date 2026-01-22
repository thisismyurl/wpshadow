<?php
declare(strict_types=1);
/**
 * Keyword Stuffing Diagnostic
 *
 * Philosophy: SEO penalties - keyword stuffing hurts rankings
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for keyword stuffing.
 */
class Diagnostic_SEO_Keyword_Stuffing extends Diagnostic_Base {
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
			LIMIT 10"
		);
		
		$issues = 0;
		foreach ( $posts as $post ) {
			$content = wp_strip_all_tags( $post->post_content );
			$words = str_word_count( $content, 1 );
			$total_words = count( $words );
			
			if ( $total_words > 0 ) {
				$word_freq = array_count_values( array_map( 'strtolower', $words ) );
				foreach ( $word_freq as $word => $count ) {
					$density = ( $count / $total_words ) * 100;
					if ( strlen( $word ) > 4 && $density > 3 ) {
						$issues++;
						break;
					}
				}
			}
		}
		
		if ( $issues > 0 ) {
			return array(
				'id'          => 'seo-keyword-stuffing',
				'title'       => 'Keyword Stuffing Detected',
				'description' => sprintf( '%d pages show signs of keyword stuffing (word density > 3%%). This can trigger search engine penalties. Use natural language and synonyms.', $issues ),
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-keyword-stuffing/',
				'training_link' => 'https://wpshadow.com/training/keyword-optimization/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}

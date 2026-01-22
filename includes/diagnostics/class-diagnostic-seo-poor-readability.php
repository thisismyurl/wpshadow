<?php declare(strict_types=1);
/**
 * Poor Readability Score Diagnostic
 *
 * Philosophy: SEO content quality - readability affects engagement
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for poor readability (Flesch Reading Ease).
 */
class Diagnostic_SEO_Poor_Readability {
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
		
		$poor = 0;
		foreach ( $posts as $post ) {
			$content = wp_strip_all_tags( $post->post_content );
			$sentences = preg_split( '/[.!?]+/', $content );
			$words = str_word_count( $content );
			$syllables = $this->count_syllables( $content );
			
			if ( count( $sentences ) > 0 && $words > 0 ) {
				$flesch = 206.835 - 1.015 * ( $words / count( $sentences ) ) - 84.6 * ( $syllables / $words );
				if ( $flesch < 50 ) {
					$poor++;
				}
			}
		}
		
		if ( $poor > 0 ) {
			return array(
				'id'          => 'seo-poor-readability',
				'title'       => 'Poor Readability Score',
				'description' => sprintf( '%d pages have poor readability (Flesch score < 50). Simplify sentences, use shorter words, break up long paragraphs.', $poor ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/improve-readability/',
				'training_link' => 'https://wpshadow.com/training/content-readability/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
	
	/**
	 * Count syllables (simplified).
	 *
	 * @param string $text Text to analyze.
	 * @return int Syllable count.
	 */
	private static function count_syllables( $text ) {
		$words = str_word_count( strtolower( $text ), 1 );
		$syllables = 0;
		foreach ( $words as $word ) {
			$syllables += max( 1, preg_match_all( '/[aeiouy]+/', $word ) );
		}
		return $syllables;
	}
}

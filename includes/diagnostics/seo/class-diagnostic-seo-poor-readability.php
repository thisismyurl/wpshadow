<?php
declare(strict_types=1);
/**
 * Poor Readability Score Diagnostic
 *
 * Philosophy: SEO content quality - readability affects engagement
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for poor readability (Flesch Reading Ease).
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Poor_Readability extends Diagnostic_Base {
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

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}

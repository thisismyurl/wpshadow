<?php
declare(strict_types=1);
/**
 * Keyword Stuffing Diagnostic
 *
 * Philosophy: SEO penalties - keyword stuffing hurts rankings
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for keyword stuffing.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
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

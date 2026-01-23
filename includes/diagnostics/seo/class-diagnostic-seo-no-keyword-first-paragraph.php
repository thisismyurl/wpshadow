<?php
declare(strict_types=1);
/**
 * No Keyword in First Paragraph Diagnostic
 *
 * Philosophy: SEO relevance - keyword early signals topic
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if target keyword appears in first paragraph.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_No_Keyword_In_First_Paragraph extends Diagnostic_Base {
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
			AND post_type = 'post' 
			LIMIT 10"
		);
		
		$issues = 0;
		foreach ( $posts as $post ) {
			$paragraphs = explode( '</p>', $post->post_content );
			if ( ! empty( $paragraphs[0] ) ) {
				$first_para = strtolower( wp_strip_all_tags( $paragraphs[0] ) );
				$title_words = explode( ' ', strtolower( $post->post_title ) );
				
				$found = false;
				foreach ( $title_words as $word ) {
					if ( strlen( $word ) > 5 && strpos( $first_para, $word ) !== false ) {
						$found = true;
						break;
					}
				}
				
				if ( ! $found ) {
					$issues++;
				}
			}
		}
		
		if ( $issues > 3 ) {
			return array(
				'id'          => 'seo-no-keyword-first-paragraph',
				'title'       => 'Keyword Not in First Paragraph',
				'description' => sprintf( '%d posts don\'t include target keyword in first paragraph. Place keyword naturally in opening 100 words to signal topic relevance.', $issues ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-first-paragraph/',
				'training_link' => 'https://wpshadow.com/training/content-structure/',
				'auto_fixable' => false,
				'threat_level' => 50,
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

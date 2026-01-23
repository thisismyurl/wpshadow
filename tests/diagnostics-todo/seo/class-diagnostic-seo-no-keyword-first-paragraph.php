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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO No Keyword In First Paragraph
	 * Slug: -seo-no-keyword-first-paragraph
	 * File: class-diagnostic-seo-no-keyword-first-paragraph.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO No Keyword In First Paragraph
	 * Slug: -seo-no-keyword-first-paragraph
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__seo_no_keyword_first_paragraph(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}

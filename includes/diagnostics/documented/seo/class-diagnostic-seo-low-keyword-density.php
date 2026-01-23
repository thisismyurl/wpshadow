<?php
declare(strict_types=1);
/**
 * Low Keyword Density Diagnostic
 *
 * Philosophy: SEO relevance - target keyword should appear naturally
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for low target keyword density.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Low_Keyword_Density extends Diagnostic_Base {
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



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Low Keyword Density
	 * Slug: -seo-low-keyword-density
	 * File: class-diagnostic-seo-low-keyword-density.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Low Keyword Density
	 * Slug: -seo-low-keyword-density
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
	public static function test_live__seo_low_keyword_density(): array {
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

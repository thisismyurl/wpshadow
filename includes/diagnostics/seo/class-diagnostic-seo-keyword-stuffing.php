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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Keyword Stuffing
	 * Slug: -seo-keyword-stuffing
	 * File: class-diagnostic-seo-keyword-stuffing.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Keyword Stuffing
	 * Slug: -seo-keyword-stuffing
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
	public static function test_live__seo_keyword_stuffing(): array {
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

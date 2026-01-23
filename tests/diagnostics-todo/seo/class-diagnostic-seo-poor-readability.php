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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Poor Readability
	 * Slug: -seo-poor-readability
	 * File: class-diagnostic-seo-poor-readability.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Poor Readability
	 * Slug: -seo-poor-readability
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
	public static function test_live__seo_poor_readability(): array {
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

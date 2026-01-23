<?php
declare(strict_types=1);
/**
 * No Keyword in URL Diagnostic
 *
 * Philosophy: SEO basics - keyword-rich URLs help rankings
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if target keyword is in URL slug.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_No_Keyword_In_URL extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_title, post_name FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type = 'post' 
			LIMIT 20"
		);
		
		$issues = 0;
		foreach ( $posts as $post ) {
			$title_words = explode( ' ', strtolower( $post->post_title ) );
			$slug = strtolower( $post->post_name );
			
			$found = false;
			foreach ( $title_words as $word ) {
				if ( strlen( $word ) > 4 && strpos( $slug, $word ) !== false ) {
					$found = true;
					break;
				}
			}
			
			if ( ! $found ) {
				$issues++;
			}
		}
		
		if ( $issues > 5 ) {
			return array(
				'id'          => 'seo-no-keyword-in-url',
				'title'       => 'Keywords Not in URLs',
				'description' => sprintf( '%d posts have URLs without target keywords. Use descriptive, keyword-rich slugs. Avoid dates and generic IDs in URLs.', $issues ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-url-structure/',
				'training_link' => 'https://wpshadow.com/training/seo-friendly-urls/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO No Keyword In URL
	 * Slug: -seo-no-keyword-url
	 * File: class-diagnostic-seo-no-keyword-url.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO No Keyword In URL
	 * Slug: -seo-no-keyword-url
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
	public static function test_live__seo_no_keyword_url(): array {
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

<?php
declare(strict_types=1);
/**
 * Long URL Structure Diagnostic
 *
 * Philosophy: SEO usability - short URLs are more shareable
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for overly long URLs.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Long_URL_Structure extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_name FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 20"
		);
		
		$long_urls = 0;
		foreach ( $posts as $post ) {
			$permalink = get_permalink( $post->ID );
			if ( strlen( $permalink ) > 75 ) {
				$long_urls++;
			}
		}
		
		if ( $long_urls > 5 ) {
			return array(
				'id'          => 'seo-long-url-structure',
				'title'       => 'URLs Too Long',
				'description' => sprintf( '%d URLs exceed 75 characters. Shorter URLs (50-60 chars) are easier to share, remember, and rank better. Simplify URL structure.', $long_urls ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/shorten-urls/',
				'training_link' => 'https://wpshadow.com/training/url-optimization/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Long URL Structure
	 * Slug: -seo-long-url-structure
	 * File: class-diagnostic-seo-long-url-structure.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Long URL Structure
	 * Slug: -seo-long-url-structure
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
	public static function test_live__seo_long_url_structure(): array {
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

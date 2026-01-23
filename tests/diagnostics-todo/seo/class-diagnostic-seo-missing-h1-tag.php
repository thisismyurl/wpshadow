<?php
declare(strict_types=1);
/**
 * Missing H1 Tag Diagnostic
 *
 * Philosophy: SEO structure - H1 is primary heading signal
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing or multiple H1 tags.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_H1_Tag extends Diagnostic_Base {
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
		
		$issues = 0;
		foreach ( $posts as $post ) {
			$h1_count = preg_match_all( '/<h1[^>]*>/i', $post->post_content );
			if ( $h1_count === 0 || $h1_count > 1 ) {
				$issues++;
			}
		}
		
		if ( $issues > 0 ) {
			return array(
				'id'          => 'seo-missing-h1-tag',
				'title'       => 'Posts Missing or Multiple H1 Tags',
				'description' => sprintf( '%d posts have either no H1 tag or multiple H1s. Each page should have exactly one H1 tag as the main heading.', $issues ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-heading-structure/',
				'training_link' => 'https://wpshadow.com/training/heading-hierarchy/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Missing H1 Tag
	 * Slug: -seo-missing-h1-tag
	 * File: class-diagnostic-seo-missing-h1-tag.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Missing H1 Tag
	 * Slug: -seo-missing-h1-tag
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
	public static function test_live__seo_missing_h1_tag(): array {
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

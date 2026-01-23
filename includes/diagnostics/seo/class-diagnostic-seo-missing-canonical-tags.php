<?php
declare(strict_types=1);
/**
 * Missing Canonical Tags Diagnostic
 *
 * Philosophy: SEO canonicalization - specify preferred URLs
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing canonical tags.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Canonical_Tags extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 10"
		);
		
		$missing = 0;
		foreach ( $posts as $post ) {
			$canonical = get_post_meta( $post->ID, '_yoast_wpseo_canonical', true );
			if ( empty( $canonical ) && ! has_action( 'wp_head', 'rel_canonical' ) ) {
				$missing++;
			}
		}
		
		if ( $missing > 0 ) {
			return array(
				'id'          => 'seo-missing-canonical-tags',
				'title'       => 'Missing Canonical Tags',
				'description' => sprintf( '%d pages missing canonical tags. Canonical tags prevent duplicate content issues. Add self-referential canonicals or use SEO plugin.', $missing ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-canonical-tags/',
				'training_link' => 'https://wpshadow.com/training/canonical-urls/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Missing Canonical Tags
	 * Slug: -seo-missing-canonical-tags
	 * File: class-diagnostic-seo-missing-canonical-tags.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Missing Canonical Tags
	 * Slug: -seo-missing-canonical-tags
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
	public static function test_live__seo_missing_canonical_tags(): array {
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

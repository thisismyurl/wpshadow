<?php
declare(strict_types=1);
/**
 * Noindex Pages Audit Diagnostic
 *
 * Philosophy: SEO visibility - accidentally noindexed pages lose traffic
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for important pages set to noindex.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Noindex_Pages_Audit extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$noindex_posts = $wpdb->get_results(
			"SELECT p.ID, p.post_title 
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_status = 'publish'
			AND p.post_type IN ('post', 'page')
			AND pm.meta_key = '_yoast_wpseo_meta-robots-noindex'
			AND pm.meta_value = '1'
			LIMIT 5"
		);
		
		if ( ! empty( $noindex_posts ) ) {
			return array(
				'id'          => 'seo-noindex-pages-audit',
				'title'       => 'Important Pages Set to Noindex',
				'description' => sprintf( '%d published pages/posts are noindexed. Accidentally noindexed pages won\'t rank. Review and remove noindex from important content.', count( $noindex_posts ) ),
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-noindex-issues/',
				'training_link' => 'https://wpshadow.com/training/indexation-control/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Noindex Pages Audit
	 * Slug: -seo-noindex-pages-audit
	 * File: class-diagnostic-seo-noindex-pages-audit.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Noindex Pages Audit
	 * Slug: -seo-noindex-pages-audit
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
	public static function test_live__seo_noindex_pages_audit(): array {
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

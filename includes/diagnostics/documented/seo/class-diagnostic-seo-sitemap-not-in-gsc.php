<?php
declare(strict_types=1);
/**
 * Sitemap Not in GSC Diagnostic
 *
 * Philosophy: SEO indexation - submit sitemap to help discovery
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if sitemap is submitted to GSC.
 */
class Diagnostic_SEO_Sitemap_Not_In_GSC extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-sitemap-not-in-gsc',
			'title'       => 'Submit Sitemap to Search Console',
			'description' => 'Ensure XML sitemap is submitted to Google Search Console. Sitemaps help Google discover and index pages faster. Submit at: Search Console > Sitemaps.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/submit-sitemap-gsc/',
			'training_link' => 'https://wpshadow.com/training/sitemap-submission/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Sitemap Not In GSC
	 * Slug: -seo-sitemap-not-in-gsc
	 * File: class-diagnostic-seo-sitemap-not-in-gsc.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Sitemap Not In GSC
	 * Slug: -seo-sitemap-not-in-gsc
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
	public static function test_live__seo_sitemap_not_in_gsc(): array {
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

<?php
declare(strict_types=1);
/**
 * No Outbound Links Diagnostic
 *
 * Philosophy: SEO trust - outbound links show research
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for posts with no outbound links.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_No_Outbound_Links extends Diagnostic_Base {
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
			LIMIT 20"
		);
		
		$no_outbound = 0;
		$home_domain = parse_url( home_url(), PHP_URL_HOST );
		
		foreach ( $posts as $post ) {
			preg_match_all( '/<a[^>]*href=["\']https?:\/\/([^"\'\/]+)/i', $post->post_content, $matches );
			
			$has_outbound = false;
			foreach ( $matches[1] as $domain ) {
				if ( $domain !== $home_domain ) {
					$has_outbound = true;
					break;
				}
			}
			
			if ( ! $has_outbound ) {
				$no_outbound++;
			}
		}
		
		if ( $no_outbound > 5 ) {
			return array(
				'id'          => 'seo-no-outbound-links',
				'title'       => 'Posts Lacking Outbound Links',
				'description' => sprintf( '%d posts have no outbound links. Link to authoritative sources (studies, data, expert sites). Shows research and builds credibility.', $no_outbound ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-outbound-links/',
				'training_link' => 'https://wpshadow.com/training/link-strategy/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO No Outbound Links
	 * Slug: -seo-no-outbound-links
	 * File: class-diagnostic-seo-no-outbound-links.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO No Outbound Links
	 * Slug: -seo-no-outbound-links
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
	public static function test_live__seo_no_outbound_links(): array {
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

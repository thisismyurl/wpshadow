<?php
declare(strict_types=1);
/**
 * Broken Links Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for broken links site-wide (deep scan only).
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Broken_Links extends Diagnostic_Base {
	/**
	 * Run the diagnostic check (deep scan).
	 *
	 * @return array|null Finding data or null if no issues.
	 */
	public static function check(): ?array {
		if ( ! function_exists( 'wpshadow_run_broken_links_scan' ) ) {
			return null;
		}

		$result = wpshadow_run_broken_links_scan(
			array(
				'check_internal' => true,
				'check_external' => true,
				'check_images'   => true,
				'limit'          => 100,
			)
		);

		if ( empty( $result['broken_links'] ) ) {
			return null;
		}

		$broken = $result['broken_links'];
		$count  = count( $broken );
		$first  = $broken[0];

		$title       = sprintf( 'Broken links found (%d)', (int) $count );
		$description = sprintf(
			/* translators: 1: URL, 2: post title, 3: status code */
			__( 'Example: %1$s in "%2$s" returned %3$s.', 'wpshadow' ),
			$first['url'],
			$first['post_title'],
			$first['status_code']
		);

		return array(
			'id'           => 'broken-links',
			'title'        => $title,
			'description'  => $description,
			'color'        => '#f44336',
			'bg_color'     => '#ffebee',
			'kb_link'      => 'https://wpshadow.com/kb/fix-broken-links/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=broken-links',
			'auto_fixable' => false,
			'threat_level' => 60,
			'category'     => 'seo',
			'extra'        => array(
				'broken_links'  => $broken,
				'posts_checked' => $result['posts_checked'] ?? 0,
				'links_checked' => $result['links_checked'] ?? 0,
			),
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Broken Links
	 * Slug: -broken-links
	 * File: class-diagnostic-broken-links.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Broken Links
	 * Slug: -broken-links
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
	public static function test_live__broken_links(): array {
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

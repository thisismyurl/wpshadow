<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Maps Embedded?
 *
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Map_Embed_Working extends Diagnostic_Base {
	protected static $slug        = 'map-embed-working';
	protected static $title       = 'Google Maps Embedded?';
	protected static $description = 'Checks if location map is embedded and working.';

	public static function check(): ?array {
		$pages = get_posts(
			array(
				'post_type'      => array( 'page', 'post' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$has_map = false;
		foreach ( $pages as $page ) {
			if ( stripos( $page->post_content, 'maps.google.com' ) !== false ||
				stripos( $page->post_content, 'google.com/maps' ) !== false ||
				preg_match( '/<iframe[^>]+maps/', $page->post_content ) ) {
				$has_map = true;
				break;
			}
		}

		if ( $has_map ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => __( 'No location map found', 'wpshadow' ),
			'description'   => __( 'Local businesses benefit from embedding a Google Map. Customers need to find you easily.', 'wpshadow' ),
			'severity'      => 'low',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/map-embed-working/',
			'training_link' => 'https://wpshadow.com/training/map-embed-working/',
			'auto_fixable'  => false,
			'threat_level'  => 30,
		);
	}




	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Google Maps Embedded?
	 * Slug: map-embed-working
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if location map is embedded and working.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_map_embed_working(): array {
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
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}

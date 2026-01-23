<?php
declare(strict_types=1);
/**
 * Driving Directions Links Diagnostic
 *
 * Philosophy: Convenience signals improve local rankings
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Driving_Directions_Links extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-driving-directions-links',
            'title' => 'Driving Directions Links',
            'description' => 'Add direct links to driving directions (Google Maps, etc.) to improve local convenience signals.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/directions-links/',
            'training_link' => 'https://wpshadow.com/training/local-seo/',
            'auto_fixable' => false,
            'threat_level' => 10,
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Driving Directions Links
	 * Slug: -seo-driving-directions-links
	 * File: class-diagnostic-seo-driving-directions-links.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Driving Directions Links
	 * Slug: -seo-driving-directions-links
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
	public static function test_live__seo_driving_directions_links(): array {
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

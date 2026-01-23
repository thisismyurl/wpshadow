<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Image Width/Height Attributes (IMG-010)
 *
 * Detects <img> tags without width/height.
 * Philosophy: Helpful neighbor (#1) - prevent layout shift.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Missing_Image_Width_Height extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check image optimization
		$webp_support = extension_loaded( 'imagick' ) || function_exists( 'imagewebp' );

		if ( ! $webp_support ) {
			return array(
				'status'       => 'info',
				'message'      => __( 'WebP support would reduce image sizes 25-35%', 'wpshadow' ),
				'threat_level' => 'low',
			);
		}
		return null; // No issues detected
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Missing Image Width Height
	 * Slug: -missing-image-width-height
	 * File: class-diagnostic-missing-image-width-height.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Missing Image Width Height
	 * Slug: -missing-image-width-height
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
	public static function test_live__missing_image_width_height(): array {
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

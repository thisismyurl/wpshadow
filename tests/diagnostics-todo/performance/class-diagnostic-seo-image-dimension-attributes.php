<?php
declare(strict_types=1);
/**
 * Image Dimension Attributes Diagnostic
 *
 * Philosophy: Provide width/height to avoid layout shift
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Image_Dimension_Attributes extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'seo-image-dimension-attributes',
			'title'         => 'Image Dimension Attributes',
			'description'   => 'Ensure images include explicit width and height to prevent layout shift (CLS).',
			'severity'      => 'low',
			'category'      => 'seo',
			'kb_link'       => 'https://wpshadow.com/kb/image-dimensions/',
			'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
			'auto_fixable'  => false,
			'threat_level'  => 20,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Image Dimension Attributes
	 * Slug: -seo-image-dimension-attributes
	 * File: class-diagnostic-seo-image-dimension-attributes.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Image Dimension Attributes
	 * Slug: -seo-image-dimension-attributes
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
	public static function test_live__seo_image_dimension_attributes(): array {
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

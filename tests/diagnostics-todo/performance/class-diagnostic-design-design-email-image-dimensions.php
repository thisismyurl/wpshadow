<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Image Dimensions
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-image-dimensions
 * Training: https://wpshadow.com/training/design-email-image-dimensions
 */
class Diagnostic_Design_DESIGN_EMAIL_IMAGE_DIMENSIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-email-image-dimensions',
            'title' => __('Email Image Dimensions', 'wpshadow'),
            'description' => __('Checks image sizing and fallbacks in email templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-image-dimensions',
            'training_link' => 'https://wpshadow.com/training/design-email-image-dimensions',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN EMAIL IMAGE DIMENSIONS
	 * Slug: -design-design-email-image-dimensions
	 * File: class-diagnostic-design-design-email-image-dimensions.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN EMAIL IMAGE DIMENSIONS
	 * Slug: -design-design-email-image-dimensions
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
	public static function test_live__design_design_email_image_dimensions(): array {
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

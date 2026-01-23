<?php
declare(strict_types=1);
/**
 * Small Font Sizes Mobile Diagnostic
 *
 * Philosophy: SEO mobile - legible text improves readability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for small font sizes on mobile.
 */
class Diagnostic_SEO_Small_Font_Sizes extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-small-font-sizes',
			'title'       => 'Check Mobile Font Sizes',
			'description' => 'Verify font size is at least 16px for body text on mobile. Smaller fonts require zooming. Use responsive typography with rem/em units.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/optimize-mobile-fonts/',
			'training_link' => 'https://wpshadow.com/training/responsive-typography/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Small Font Sizes
	 * Slug: -seo-small-font-sizes
	 * File: class-diagnostic-seo-small-font-sizes.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Small Font Sizes
	 * Slug: -seo-small-font-sizes
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
	public static function test_live__seo_small_font_sizes(): array {
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

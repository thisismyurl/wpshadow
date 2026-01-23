<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Weight Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-font-weight-scale
 * Training: https://wpshadow.com/training/design-system-font-weight-scale
 */
class Diagnostic_Design_SYSTEM_FONT_WEIGHT_SCALE extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-system-font-weight-scale',
			'title'         => __( 'Font Weight Enforcement', 'wpshadow' ),
			'description'   => __( 'Verifies only system-defined weights used (400, 600, 700).', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-system-font-weight-scale',
			'training_link' => 'https://wpshadow.com/training/design-system-font-weight-scale',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design SYSTEM FONT WEIGHT SCALE
	 * Slug: -design-system-font-weight-scale
	 * File: class-diagnostic-design-system-font-weight-scale.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design SYSTEM FONT WEIGHT SCALE
	 * Slug: -design-system-font-weight-scale
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
	public static function test_live__design_system_font_weight_scale(): array {
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

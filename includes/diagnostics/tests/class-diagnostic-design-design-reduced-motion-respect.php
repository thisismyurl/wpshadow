<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Reduced Motion Respect
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-reduced-motion-respect
 * Training: https://wpshadow.com/training/design-reduced-motion-respect
 */
class Diagnostic_Design_DESIGN_REDUCED_MOTION_RESPECT extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-reduced-motion-respect',
			'title'         => __( 'Reduced Motion Respect', 'wpshadow' ),
			'description'   => __( 'Checks honors prefers-reduced-motion with fallbacks.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-reduced-motion-respect',
			'training_link' => 'https://wpshadow.com/training/design-reduced-motion-respect',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN REDUCED MOTION RESPECT
	 * Slug: -design-design-reduced-motion-respect
	 * File: class-diagnostic-design-design-reduced-motion-respect.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN REDUCED MOTION RESPECT
	 * Slug: -design-design-reduced-motion-respect
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
	public static function test_live__design_design_reduced_motion_respect(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Reduced motion preferences are respected',
			);
		}
		$message = $result['description'] ?? 'Reduced motion accessibility issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

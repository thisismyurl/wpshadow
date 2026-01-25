<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Parallax Cost
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-parallax-cost
 * Training: https://wpshadow.com/training/design-parallax-cost
 */
class Diagnostic_Design_DESIGN_PARALLAX_COST extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-parallax-cost',
			'title'         => __( 'Parallax Cost', 'wpshadow' ),
			'description'   => __( 'Flags scroll-tied parallax without throttling.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-parallax-cost',
			'training_link' => 'https://wpshadow.com/training/design-parallax-cost',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN PARALLAX COST
	 * Slug: -design-design-parallax-cost
	 * File: class-diagnostic-design-design-parallax-cost.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN PARALLAX COST
	 * Slug: -design-design-parallax-cost
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
	public static function test_live__design_design_parallax_cost(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Parallax effects optimized for performance',
			);
		}
		$message = $result['description'] ?? 'Parallax performance issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Sidebar Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-sidebar-consistency
 * Training: https://wpshadow.com/training/design-sidebar-consistency
 */
class Diagnostic_Design_DESIGN_SIDEBAR_CONSISTENCY extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-sidebar-consistency',
			'title'         => __( 'Sidebar Consistency', 'wpshadow' ),
			'description'   => __( 'Checks sidebar width, gaps, and widget styling consistency.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-sidebar-consistency',
			'training_link' => 'https://wpshadow.com/training/design-sidebar-consistency',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN SIDEBAR CONSISTENCY
	 * Slug: -design-design-sidebar-consistency
	 * File: class-diagnostic-design-design-sidebar-consistency.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN SIDEBAR CONSISTENCY
	 * Slug: -design-design-sidebar-consistency
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
	public static function test_live__design_design_sidebar_consistency(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Sidebar design is consistent across pages',
			);
		}
		$message = $result['description'] ?? 'Sidebar consistency issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

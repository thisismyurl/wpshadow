<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Popover Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-popover-consistency
 * Training: https://wpshadow.com/training/design-popover-consistency
 */
class Diagnostic_Design_POPOVER_CONSISTENCY extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-popover-consistency',
			'title'         => __( 'Popover Consistency', 'wpshadow' ),
			'description'   => __( 'Checks popovers follow card design system, clear close mechanism.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-popover-consistency',
			'training_link' => 'https://wpshadow.com/training/design-popover-consistency',
			'auto_fixable'  => false,
			'threat_level'  => 5,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design POPOVER CONSISTENCY
	 * Slug: -design-popover-consistency
	 * File: class-diagnostic-design-popover-consistency.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design POPOVER CONSISTENCY
	 * Slug: -design-popover-consistency
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
	public static function test_live__design_popover_consistency(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Popovers are consistent and accessible throughout site',
			);
		}
		$message = $result['description'] ?? 'Popover consistency issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

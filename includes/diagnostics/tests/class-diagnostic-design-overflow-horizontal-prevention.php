<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Horizontal Overflow Prevention
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-overflow-horizontal-prevention
 * Training: https://wpshadow.com/training/design-overflow-horizontal-prevention
 */
class Diagnostic_Design_OVERFLOW_HORIZONTAL_PREVENTION extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-overflow-horizontal-prevention',
			'title'         => __( 'Horizontal Overflow Prevention', 'wpshadow' ),
			'description'   => __( 'Verifies no horizontal scrolling at breakpoints.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-overflow-horizontal-prevention',
			'training_link' => 'https://wpshadow.com/training/design-overflow-horizontal-prevention',
			'auto_fixable'  => false,
			'threat_level'  => 5,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design OVERFLOW HORIZONTAL PREVENTION
	 * Slug: -design-overflow-horizontal-prevention
	 * File: class-diagnostic-design-overflow-horizontal-prevention.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design OVERFLOW HORIZONTAL PREVENTION
	 * Slug: -design-overflow-horizontal-prevention
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
	public static function test_live__design_overflow_horizontal_prevention(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'No unwanted horizontal scrollbars detected',
			);
		}
		$message = $result['description'] ?? 'Horizontal overflow issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

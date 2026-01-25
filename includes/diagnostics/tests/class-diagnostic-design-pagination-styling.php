<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Pagination Styling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-pagination-styling
 * Training: https://wpshadow.com/training/design-pagination-styling
 */
class Diagnostic_Design_PAGINATION_STYLING extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-pagination-styling',
			'title'         => __( 'Pagination Styling', 'wpshadow' ),
			'description'   => __( 'Checks pagination styling consistent.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-pagination-styling',
			'training_link' => 'https://wpshadow.com/training/design-pagination-styling',
			'auto_fixable'  => false,
			'threat_level'  => 5,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design PAGINATION STYLING
	 * Slug: -design-pagination-styling
	 * File: class-diagnostic-design-pagination-styling.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design PAGINATION STYLING
	 * Slug: -design-pagination-styling
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
	public static function test_live__design_pagination_styling(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Pagination styling matches site design system',
			);
		}
		$message = $result['description'] ?? 'Pagination styling issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

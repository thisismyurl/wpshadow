<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Pagination Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-pagination-consistency
 * Training: https://wpshadow.com/training/design-pagination-consistency
 */
class Diagnostic_Design_PAGINATION_CONSISTENCY extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-pagination-consistency',
			'title'         => __( 'Pagination Consistency', 'wpshadow' ),
			'description'   => __( 'Checks pagination styled consistently across post types.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-pagination-consistency',
			'training_link' => 'https://wpshadow.com/training/design-pagination-consistency',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design PAGINATION CONSISTENCY
	 * Slug: -design-pagination-consistency
	 * File: class-diagnostic-design-pagination-consistency.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design PAGINATION CONSISTENCY
	 * Slug: -design-pagination-consistency
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
	public static function test_live__design_pagination_consistency(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Pagination is consistent across all archive pages',
			);
		}
		$message = $result['description'] ?? 'Pagination consistency issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

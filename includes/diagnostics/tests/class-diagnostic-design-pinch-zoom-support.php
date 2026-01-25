<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Pinch Zoom Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-pinch-zoom-support
 * Training: https://wpshadow.com/training/design-pinch-zoom-support
 */
class Diagnostic_Design_PINCH_ZOOM_SUPPORT extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-pinch-zoom-support',
			'title'         => __( 'Pinch Zoom Support', 'wpshadow' ),
			'description'   => __( 'Confirms pinch-zoom functional.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-pinch-zoom-support',
			'training_link' => 'https://wpshadow.com/training/design-pinch-zoom-support',
			'auto_fixable'  => false,
			'threat_level'  => 5,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design PINCH ZOOM SUPPORT
	 * Slug: -design-pinch-zoom-support
	 * File: class-diagnostic-design-pinch-zoom-support.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design PINCH ZOOM SUPPORT
	 * Slug: -design-pinch-zoom-support
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
	public static function test_live__design_pinch_zoom_support(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Pinch-to-zoom functionality is enabled and working',
			);
		}
		$message = $result['description'] ?? 'Pinch zoom support issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

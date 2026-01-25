<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Visual extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'monitor-visual_stability_trend',
			'title'         => __( 'Visual Stability Trend', 'wpshadow' ),
			'description'   => __( 'Monitors layout shift patterns. Instability = poor UX signal.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/',
			'training_link' => 'https://wpshadow.com/training/',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Visual
	 * Slug: -monitor-visual-stability-trend
	 * File: class-diagnostic-monitor-visual-stability-trend.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Visual
	 * Slug: -monitor-visual-stability-trend
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
	public static function test_live__monitor_visual_stability_trend(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Visual stability is excellent and improving',
			);
		}
		$message = $result['description'] ?? 'Visual stability concern detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

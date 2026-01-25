<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Update extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'monitor-update_frequency',
			'title'         => __( 'Update Frequency Analysis', 'wpshadow' ),
			'description'   => __( 'Tracks update patterns. Consistency signals expertise; infrequent = outdated site.', 'wpshadow' ),
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
	 * Diagnostic: Monitor Update
	 * Slug: -monitor-update-frequency
	 * File: class-diagnostic-monitor-update-frequency.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Update
	 * Slug: -monitor-update-frequency
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
	public static function test_live__monitor_update_frequency(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Content is updated regularly and frequently',
			);
		}
		$message = $result['description'] ?? 'Update frequency concern detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

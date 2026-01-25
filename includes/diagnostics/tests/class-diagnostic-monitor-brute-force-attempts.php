<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Monitor_Brute_Force_Attempts extends Diagnostic_Base {

	public static function check(): ?array {
		// Check if monitoring plugins are active
		$has_monitoring = is_plugin_active( 'wordfence/wordfence.php' ) ||
			is_plugin_active( 'sucuri-scanner/sucuri.php' );
		if ( $has_monitoring ) {
			return null; // Monitoring in place
		}

		return array(
			'id'            => 'monitor-brute-force',
			'title'         => __( 'Brute Force Attack Detection', 'wpshadow' ),
			'description'   => __( 'Detects multiple failed login attempts from same IP. Real-time alert enables quick block before accounts compromised.', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/login-security/',
			'training_link' => 'https://wpshadow.com/training/brute-force-prevention/',
			'auto_fixable'  => false,
			'threat_level'  => 9,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Brute Force Attempts
	 * Slug: -monitor-brute-force-attempts
	 * File: class-diagnostic-monitor-brute-force-attempts.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Brute Force Attempts
	 * Slug: -monitor-brute-force-attempts
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
	public static function test_live__monitor_brute_force_attempts(): array {
		$has_monitoring = is_plugin_active( 'wordfence/wordfence.php' ) || is_plugin_active( 'sucuri-scanner/sucuri.php' );

		$diagnostic_result    = self::check();
		$should_find_issue    = ( ! $has_monitoring );
		$diagnostic_has_issue = ( null !== $diagnostic_result );
		$test_passes          = ( $should_find_issue === $diagnostic_has_issue );

		$message = sprintf(
			'Brute-force monitoring active: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$has_monitoring ? 'YES' : 'NO',
			$should_find_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

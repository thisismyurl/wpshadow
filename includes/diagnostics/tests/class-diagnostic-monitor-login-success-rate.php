<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Login_Success_Rate extends Diagnostic_Base {

	public static function check(): ?array {
		// Check if monitoring plugins are active
		$has_monitoring = is_plugin_active( 'wordfence/wordfence.php' ) ||
			is_plugin_active( 'sucuri-scanner/sucuri.php' );
		if ( $has_monitoring ) {
			return null; // Monitoring in place
		}

		return array(
			'id'            => 'monitor-login-success',
			'title'         => __( 'Login Success Rate', 'wpshadow' ),
			'description'   => __( 'Tracks successful logins. Drop indicates auth system failure, 2FA issues, or password reset malfunction.', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/auth-monitoring/',
			'training_link' => 'https://wpshadow.com/training/authentication/',
			'auto_fixable'  => false,
			'threat_level'  => 8,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Login Success Rate
	 * Slug: -monitor-login-success-rate
	 * File: class-diagnostic-monitor-login-success-rate.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Login Success Rate
	 * Slug: -monitor-login-success-rate
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
	public static function test_live__monitor_login_success_rate(): array {
		$has_monitoring = is_plugin_active( 'wordfence/wordfence.php' ) || is_plugin_active( 'sucuri-scanner/sucuri.php' );

		$diagnostic_result    = self::check();
		$should_find_issue    = ( ! $has_monitoring );
		$diagnostic_has_issue = ( null !== $diagnostic_result );
		$test_passes          = ( $should_find_issue === $diagnostic_has_issue );

		$message = sprintf(
			'Login monitoring active: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
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

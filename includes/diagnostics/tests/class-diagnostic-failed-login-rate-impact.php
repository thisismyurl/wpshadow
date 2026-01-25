<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Failed Login Rate Monitoring (SECURITY-PERF-004)
 *
 * Tracks failed login attempts and their performance impact.
 * Philosophy: Show value (#9) - Reduce wasted resources on invalid logins.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Failed_Login_Rate_Impact extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Security check implementation
		// Track failed login rate impact
		global $wpdb;

		// Check for excessive failed login attempts in last 30 minutes
		$table           = $wpdb->prefix . 'users';
		$recent_failures = get_transient( 'wpshadow_recent_login_failures' );

		if ( $recent_failures && $recent_failures > 10 ) {
			return array(
				'id'            => 'failed-login-rate-impact',
				'title'         => __( 'High Failed Login Rate', 'wpshadow' ),
				'description'   => sprintf( __( 'Detected %d failed login attempts in recent period. Enable login limiting and consider IP-based blocking.', 'wpshadow' ), $recent_failures ),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/login-rate-limiting/',
				'training_link' => 'https://wpshadow.com/training/failed-login-protection/',
				'auto_fixable'  => false,
				'threat_level'  => 80,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Failed Login Rate Impact
	 * Slug: -failed-login-rate-impact
	 * File: class-diagnostic-failed-login-rate-impact.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Failed Login Rate Impact
	 * Slug: -failed-login-rate-impact
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
	public static function test_live__failed_login_rate_impact(): array {
		$recent_failures = (int) get_transient( 'wpshadow_recent_login_failures' );
		$threshold       = 10; // Must match check() logic

		$diagnostic_result    = self::check();
		$should_find_issue    = ( $recent_failures > $threshold );
		$diagnostic_has_issue = ( null !== $diagnostic_result );
		$test_passes          = ( $should_find_issue === $diagnostic_has_issue );

		$message = sprintf(
			'Recent failed logins: %d (threshold: %d). Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$recent_failures,
			$threshold,
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

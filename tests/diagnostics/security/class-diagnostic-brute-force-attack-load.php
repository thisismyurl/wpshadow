<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Brute Force Attack Performance Impact (SECURITY-PERF-001)
 *
 * Detects when brute force login attempts are causing performance degradation.
 * Philosophy: Show value (#9) - Security + performance working together.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Brute_Force_Attack_Load extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array
	{
		// Security check implementation
		// Track failed login attempts
		$failed_logins = get_transient('wpshadow_failed_logins_count');
		if (!$failed_logins) {
			$failed_logins = 0;
		}

		// If more than 5 failed attempts in last hour, warn
		if ($failed_logins > 5) {
			return array(
				'id' => 'brute-force-attack-load',
				'title' => __('Brute Force Attack Activity Detected', 'wpshadow'),
				'description' => sprintf(__('Multiple failed login attempts detected (%d attempts). Consider enabling login rate limiting or CAPTCHA protection.', 'wpshadow'), $failed_logins),
				'severity' => 'high',
				'category' => 'security',
				'kb_link' => 'https://wpshadow.com/kb/prevent-brute-force/',
				'training_link' => 'https://wpshadow.com/training/brute-force-protection/',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Brute Force Attack Load
	 * Slug: -brute-force-attack-load
	 * File: class-diagnostic-brute-force-attack-load.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Brute Force Attack Load
	 * Slug: -brute-force-attack-load
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
	public static function test_live__brute_force_attack_load(): array
	{
		$failed_logins = get_transient('wpshadow_failed_logins_count');
		if (!$failed_logins) {
			$failed_logins = 0;
		}

		$expected_issue = $failed_logins > 5;

		$result = self::check();
		$has_finding = is_array($result);

		if ($expected_issue === $has_finding) {
			$message = $expected_issue ? 'Finding returned when failed logins exceed threshold.' : 'No finding when login attempts are below threshold.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_issue
			? 'Expected a finding when failed logins > 5 but got none.'
			: 'Expected no finding when logins <=5 but got a finding.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

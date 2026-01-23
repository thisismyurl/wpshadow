<?php

declare(strict_types=1);
/**
 * Login Attempt Logging Diagnostic
 *
 * Philosophy: Forensics and monitoring - track login attempts
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if failed login attempts are logged.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Login_Logs extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check for login logging plugins or features
		$logging_plugins = array(
			'wordfence/wordfence.php',
			'simple-login-log/simple-login-log.php',
			'wp-security-audit-log/wp-security-audit-log.php',
			'user-activity-log/user-activity-log.php',
		);

		$active = get_option('active_plugins', array());
		foreach ($logging_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				return null; // Login logging active
			}
		}

		return array(
			'id'          => 'login-logs',
			'title'       => 'Login Attempts Not Logged',
			'description' => 'Failed login attempts are not being logged, preventing forensic analysis of security incidents. Enable login logging via a security plugin.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-login-logging/',
			'training_link' => 'https://wpshadow.com/training/login-monitoring/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Login Logs
	 * Slug: -login-logs
	 * File: class-diagnostic-login-logs.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Login Logs
	 * Slug: -login-logs
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
	public static function test_live__login_logs(): array
	{
		$logging_plugins = array(
			'wordfence/wordfence.php',
			'simple-login-log/simple-login-log.php',
			'wp-security-audit-log/wp-security-audit-log.php',
			'user-activity-log/user-activity-log.php',
		);

		$active = get_option('active_plugins', array());
		$has_logging = false;
		foreach ($logging_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				$has_logging = true;
				break;
			}
		}

		$diagnostic_result    = self::check();
		$should_find_issue    = (! $has_logging);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Login logging active: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$has_logging ? 'YES' : 'NO',
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

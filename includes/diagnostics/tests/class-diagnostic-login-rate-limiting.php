<?php

declare(strict_types=1);
/**
 * Login Rate Limiting Diagnostic
 *
 * Philosophy: Security hardening - prevents brute force attacks
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if login rate limiting is enabled.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Login_Rate_Limiting extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check for common rate limiting plugins
		$rate_limit_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'limit-login-attempts/limit-login-attempts.php',
			'wordfence/wordfence.php',
			'better-wp-security/better-wp-security.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);

		$active = get_option('active_plugins', array());
		foreach ($rate_limit_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				return null; // Rate limiting plugin active
			}
		}

		return array(
			'id'          => 'login-rate-limiting',
			'title'       => 'Login Rate Limiting Not Enabled',
			'description' => 'Your site lacks login attempt rate limiting, making it vulnerable to brute force attacks. Install a rate limiting plugin or configure server-side protection.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-login-rate-limiting/',
			'training_link' => 'https://wpshadow.com/training/login-rate-limiting/',
			'auto_fixable' => false,
			'threat_level' => 80,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Login Rate Limiting
	 * Slug: -login-rate-limiting
	 * File: class-diagnostic-login-rate-limiting.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Login Rate Limiting
	 * Slug: -login-rate-limiting
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
	public static function test_live__login_rate_limiting(): array
	{
		$rate_limit_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'limit-login-attempts/limit-login-attempts.php',
			'wordfence/wordfence.php',
			'better-wp-security/better-wp-security.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);

		$active = get_option('active_plugins', array());
		$has_rate_limit = false;
		foreach ($rate_limit_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				$has_rate_limit = true;
				break;
			}
		}

		$diagnostic_result    = self::check();
		$should_find_issue    = (! $has_rate_limit);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Rate limiting plugin active: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$has_rate_limit ? 'YES' : 'NO',
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

<?php

declare(strict_types=1);
/**
 * Force Strong Passwords Diagnostic
 *
 * Philosophy: Access control - require secure passwords
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if strong passwords are enforced.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Force_Strong_Passwords extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$password_plugins = array(
			'wp-password-bcrypt/wp-password-bcrypt.php',
			'force-strong-passwords/force-strong-passwords.php',
		);

		$active = get_option('active_plugins', array());
		foreach ($password_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				return null;
			}
		}

		return array(
			'id'          => 'force-strong-passwords',
			'title'       => 'No Password Strength Requirement',
			'description' => 'Users can set weak passwords. Enforce minimum password complexity (8+ chars, mixed case, numbers, symbols) to prevent brute force attacks.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enforce-password-strength/',
			'training_link' => 'https://wpshadow.com/training/password-security/',
			'auto_fixable' => false,
			'threat_level' => 65,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Force Strong Passwords
	 * Slug: -force-strong-passwords
	 * File: class-diagnostic-force-strong-passwords.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Force Strong Passwords
	 * Slug: -force-strong-passwords
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
	public static function test_live__force_strong_passwords(): array
	{
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		$password_plugins = array(
			'wp-password-bcrypt/wp-password-bcrypt.php',
			'force-strong-passwords/force-strong-passwords.php',
		);

		$active = get_option('active_plugins', array());
		$has_strong_pw = false;
		foreach ($password_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				$has_strong_pw = true;
				break;
			}
		}

		$should_pass = $has_strong_pw;
		$diagnostic_passed = is_null($result);
		$test_passes = ($should_pass === $diagnostic_passed);

		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Strong password check matches site state' :
				"Mismatch: expected " . ($should_pass ? 'pass' : 'fail') . " but got " .
				($diagnostic_passed ? 'pass' : 'fail'),
		);
	}
}

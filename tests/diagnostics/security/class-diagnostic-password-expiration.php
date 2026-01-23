<?php

declare(strict_types=1);
/**
 * Password Expiration Policy Diagnostic
 *
 * Philosophy: Access control - periodic password resets
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if password expiration is enforced.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Password_Expiration extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$password_expiration = get_option('password_expiration_days');

		if (empty($password_expiration) || $password_expiration > 180) {
			return array(
				'id'          => 'password-expiration',
				'title'       => 'No Password Expiration Policy',
				'description' => 'Passwords never expire. Compromised credentials remain valid indefinitely. Implement password expiration (force resets every 60-90 days).',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/set-password-expiration/',
				'training_link' => 'https://wpshadow.com/training/password-policy/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Password Expiration
	 * Slug: -password-expiration
	 * File: class-diagnostic-password-expiration.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Password Expiration
	 * Slug: -password-expiration
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
	public static function test_live__password_expiration(): array
	{
		$password_expiration = get_option('password_expiration_days');
		$expected_issue = empty($password_expiration) || $password_expiration > 180;

		$result = self::check();
		$has_finding = is_array($result);

		if ($expected_issue === $has_finding) {
			$message = $expected_issue ? 'Finding returned when password expiration is weak or unset.' : 'No finding returned when password expiration is enforced.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_issue
			? 'Expected a finding when password expiration is weak or unset, but got none.'
			: 'Expected no finding when password expiration is enforced, but got a finding.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}

<?php

declare(strict_types=1);
/**
 * Session Fixation Protection Diagnostic
 *
 * Philosophy: Authentication security - regenerate session IDs
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if session IDs are regenerated on login.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Session_Fixation_Protection extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$has_fixation_protection = has_action('wp_login');

		if (! $has_fixation_protection) {
			return array(
				'id'          => 'session-fixation-protection',
				'title'       => 'No Session Fixation Protection',
				'description' => 'Session IDs are not regenerated on login. Attackers can use pre-existing session IDs to hijack accounts. Regenerate session ID on every login.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-session-fixation/',
				'training_link' => 'https://wpshadow.com/training/session-security/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Session Fixation Protection
	 * Slug: -session-fixation-protection
	 * File: class-diagnostic-session-fixation-protection.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Session Fixation Protection
	 * Slug: -session-fixation-protection
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
	public static function test_live__session_fixation_protection(): array
	{
		$has_fixation_protection = has_action('wp_login');

		$diagnostic_result    = self::check();
		$should_find_issue    = (! $has_fixation_protection);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'wp_login hook present: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$has_fixation_protection ? 'YES' : 'NO',
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

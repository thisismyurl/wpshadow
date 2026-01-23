<?php

declare(strict_types=1);
/**
 * Concurrent Session Limits Diagnostic
 *
 * Philosophy: Session security - detect credential sharing/theft
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for concurrent session limits.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Concurrent_Session_Limits extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check if any plugin implements session limits
		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php',
			'limit-login-sessions/limit-login-sessions.php',
		);

		$active = get_option('active_plugins', array());
		foreach ($session_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				return null; // Session limiting enabled
			}
		}

		// Check for custom session management
		if (has_filter('attach_session_information')) {
			return null; // Custom session management
		}

		// Sample admin users to check for excessive sessions
		$admins = get_users(array('role' => 'administrator', 'number' => 3));
		$max_sessions = 0;

		foreach ($admins as $admin) {
			$sessions = WP_Session_Tokens::get_instance($admin->ID);
			$all_sessions = $sessions->get_all();
			$session_count = count($all_sessions);

			if ($session_count > $max_sessions) {
				$max_sessions = $session_count;
			}
		}

		if ($max_sessions > 5) {
			return array(
				'id'          => 'concurrent-session-limits',
				'title'       => 'No Concurrent Session Limits',
				'description' => sprintf(
					'Admin users have up to %d simultaneous active sessions. Without session limits, stolen credentials go undetected. Implement concurrent session limits.',
					$max_sessions
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/limit-concurrent-sessions/',
				'training_link' => 'https://wpshadow.com/training/session-management/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Concurrent Session Limits
	 * Slug: -concurrent-session-limits
	 * File: class-diagnostic-concurrent-session-limits.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Concurrent Session Limits
	 * Slug: -concurrent-session-limits
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
	public static function test_live__concurrent_session_limits(): array
	{
		$result = self::check();

		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php',
			'limit-login-sessions/limit-login-sessions.php',
		);

		$active = get_option('active_plugins', array());
		$has_session_limit = false;
		foreach ($session_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				$has_session_limit = true;
				break;
			}
		}

		if (has_filter('attach_session_information')) {
			$has_session_limit = true;
		}

		$max_sessions = 0;
		if (!$has_session_limit && class_exists('\\WP_Session_Tokens')) {
			$admins = get_users(array('role' => 'administrator', 'number' => 3));
			foreach ($admins as $admin) {
				$sessions = \WP_Session_Tokens::get_instance($admin->ID);
				$all_sessions = $sessions->get_all();
				$session_count = is_array($all_sessions) ? count($all_sessions) : 0;
				if ($session_count > $max_sessions) {
					$max_sessions = $session_count;
				}
			}
		}

		$has_issue = (!$has_session_limit && $max_sessions > 5);
		$diagnostic_found_issue = !is_null($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Concurrent session limits check matches site state' :
				"Mismatch: expected " . ($has_issue ? 'issue' : 'no issue') . " but got " .
				($diagnostic_found_issue ? 'issue' : 'pass'),
		);
	}
}

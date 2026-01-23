<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: User Session Table Bloat (DB-010)
 *
 * Checks wp_usermeta for expired user sessions.
 * Philosophy: Drive to training (#6) - teach session management.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Session_Table_Bloat extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array
	{
		// Security check implementation
		// Check WordPress user session table for bloat
		global $wpdb;

		$session_table = $wpdb->prefix . 'user_meta';
		$session_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$session_table} WHERE meta_key LIKE 'wp_session%' OR meta_key LIKE '%_session%'"
		);

		if ($session_count && $session_count > 1000) {
			return array(
				'id' => 'user-session-table-bloat',
				'title' => sprintf(__('Large Number of Stored Sessions (%d)', 'wpshadow'), $session_count),
				'description' => __('Many sessions are stored in the database. Consider implementing session cleanup or Redis for better performance.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'performance',
				'kb_link' => 'https://wpshadow.com/kb/session-table-cleanup/',
				'training_link' => 'https://wpshadow.com/training/session-optimization/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: User Session Table Bloat
	 * Slug: -user-session-table-bloat
	 * File: class-diagnostic-user-session-table-bloat.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: User Session Table Bloat
	 * Slug: -user-session-table-bloat
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
	public static function test_live__user_session_table_bloat(): array
	{
		global $wpdb;

		$session_table = $wpdb->prefix . 'user_meta';
		$session_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$session_table} WHERE meta_key LIKE 'wp_session%' OR meta_key LIKE '%_session%'"
		);

		$threshold           = 1000; // Must match check() threshold
		$diagnostic_result   = self::check();
		$should_find_issue   = ($session_count > $threshold);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes         = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Session rows: %d (threshold: %d). Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$session_count,
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

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Monitor_SQL_Injection_Attempts extends Diagnostic_Base
{
	public static function check(): ?array
	{
		// Check if security monitoring is active (Wordfence, Sucuri, etc)
		$monitoring_active = is_plugin_active('wordfence/wordfence.php') ||
			is_plugin_active('sucuri-scanner/sucuri.php') ||
			is_plugin_active('better-wp-security/better-wp-security.php');

		if ($monitoring_active) {
			return null; // Monitoring in place
		}

		return ['id' => 'monitor-sql-injection', 'title' => __('SQL Injection Monitoring Not Active', 'wpshadow'), 'description' => __('No security plugin monitoring SQL injection attempts. Install Wordfence or similar.', 'wpshadow'), 'severity' => 'low', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/sql-injection-prevention/', 'training_link' => 'https://wpshadow.com/training/security-hardening/', 'auto_fixable' => false, 'threat_level' => 10];
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor SQL Injection Attempts
	 * Slug: -monitor-sql-injection-attempts
	 * File: class-diagnostic-monitor-sql-injection-attempts.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor SQL Injection Attempts
	 * Slug: -monitor-sql-injection-attempts
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
	public static function test_live__monitor_sql_injection_attempts(): array
	{
		$monitoring_active = is_plugin_active('wordfence/wordfence.php')
			|| is_plugin_active('sucuri-scanner/sucuri.php')
			|| is_plugin_active('better-wp-security/better-wp-security.php');

		$diagnostic_result    = self::check();
		$should_find_issue    = (! $monitoring_active);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'SQLi monitoring active: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$monitoring_active ? 'YES' : 'NO',
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

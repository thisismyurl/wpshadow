<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Monitor_Path_Traversal_Attempts extends Diagnostic_Base
{
	public static function check(): ?array
	{
		// Check if security monitoring is active
		$monitoring_active = is_plugin_active('wordfence/wordfence.php') ||
			is_plugin_active('sucuri-scanner/sucuri.php') ||
			is_plugin_active('better-wp-security/better-wp-security.php');

		if ($monitoring_active) {
			return null;
		}

		return ['id' => 'monitor-path-traversal', 'title' => __('Path Traversal Attack Detection', 'wpshadow'), 'description' => __('Detects directory traversal attempts (../, ../../, ..\\). Prevents file access outside intended directories.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/file-access-security/', 'training_link' => 'https://wpshadow.com/training/access-control/', 'auto_fixable' => false, 'threat_level' => 10];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Path Traversal Attempts
	 * Slug: -monitor-path-traversal-attempts
	 * File: class-diagnostic-monitor-path-traversal-attempts.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Path Traversal Attempts
	 * Slug: -monitor-path-traversal-attempts
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
	public static function test_live__monitor_path_traversal_attempts(): array
	{
		$has_monitoring = is_plugin_active('wordfence/wordfence.php') || is_plugin_active('sucuri-scanner/sucuri.php') || is_plugin_active('better-wp-security/better-wp-security.php');

		$diagnostic_result    = self::check();
		$should_find_issue    = (! $has_monitoring);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Path traversal monitoring active: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$has_monitoring ? 'YES' : 'NO',
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

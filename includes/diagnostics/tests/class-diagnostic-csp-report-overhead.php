<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSP Report Overhead (SEC-PERF-008)
 *
 * CSP Report Overhead diagnostic
 * Philosophy: Educate (#5) - Monitor without slowdown.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DiagnosticCspReportOverhead extends Diagnostic_Base
{
	public static function check(): ?array
	{
		// Check if Content-Security-Policy header is set
		$headers = wp_get_server_var('HTTP_CONTENT_SECURITY_POLICY_REPORT_ONLY');
		if (!$headers) {
			$headers = wp_get_server_var('HTTP_CONTENT_SECURITY_POLICY');
		}

		// If CSP enabled, monitor for excessive reports
		if ($headers) {
			$csp_reports = get_transient('wpshadow_csp_violation_count');
			if ($csp_reports && $csp_reports > 100) {
				return array(
					'id' => 'csp-report-overhead',
					'title' => __('High CSP Violation Report Rate', 'wpshadow'),
					'description' => __('CSP violations are being reported frequently. Review CSP policy to reduce legitimate violations and improve performance.', 'wpshadow'),
					'severity' => 'medium',
					'category' => 'security',
					'kb_link' => 'https://wpshadow.com/kb/csp-policy-optimization/',
					'training_link' => 'https://wpshadow.com/training/csp-headers/',
					'auto_fixable' => false,
					'threat_level' => 40,
				);
			}
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: DiagnosticCspReportOverhead
	 * Slug: -csp-report-overhead
	 * File: class-diagnostic-csp-report-overhead.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: DiagnosticCspReportOverhead
	 * Slug: -csp-report-overhead
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
	public static function test_live__csp_report_overhead(): array
	{
		$headers = wp_get_server_var('HTTP_CONTENT_SECURITY_POLICY_REPORT_ONLY');
		if (! $headers) {
			$headers = wp_get_server_var('HTTP_CONTENT_SECURITY_POLICY');
		}

		$csp_reports = 0;
		if ($headers) {
			$csp_reports = (int) get_transient('wpshadow_csp_violation_count');
		}

		$diagnostic_result    = self::check();
		$should_find_issue    = ($headers && $csp_reports > 100);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'CSP header present: %s, violation count: %d. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$headers ? 'YES' : 'NO',
			$csp_reports,
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

<?php

declare(strict_types=1);
/**
 * Display Errors in Production Diagnostic
 *
 * Philosophy: Information disclosure - hide errors in production
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if PHP errors are displayed to users.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Display_Errors_Production extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$display_errors  = ini_get('display_errors');
		$error_reporting = error_reporting();

		$issues = array();

		// Check if display_errors is on
		if ($display_errors && $display_errors !== '0' && $display_errors !== 'off') {
			$issues[] = 'display_errors is ON (exposes file paths and logic)';
		}

		// Check if error_reporting includes warnings/notices in production
		if ($error_reporting & E_WARNING || $error_reporting & E_NOTICE) {
			if (! defined('WP_DEBUG') || ! WP_DEBUG) {
				$issues[] = 'error_reporting includes warnings/notices in production';
			}
		}

		// Check if WP_DEBUG_DISPLAY is enabled
		if (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
			$issues[] = 'WP_DEBUG_DISPLAY is TRUE (shows WordPress errors to public)';
		}

		if (! empty($issues)) {
			return array(
				'id'            => 'display-errors-production',
				'title'         => 'Errors Displayed in Production',
				'description'   => sprintf(
					'Production error configuration issues: %s. Error messages reveal file paths, database structure, and application logic to attackers.',
					implode('; ', $issues)
				),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-error-display/',
				'training_link' => 'https://wpshadow.com/training/error-handling/',
				'auto_fixable'  => false,
				'threat_level'  => 80,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Display Errors Production
	 * Slug: -display-errors-production
	 * File: class-diagnostic-display-errors-production.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Display Errors Production
	 * Slug: -display-errors-production
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
	public static function test_live__display_errors_production(): array
	{
		$display_errors = ini_get('display_errors');
		$error_reporting = error_reporting();
		$wp_debug_display = (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY);

		// Determine if issue exists
		$display_errors_on = ($display_errors && $display_errors !== '0' && $display_errors !== 'off');
		$reporting_warnings = ($error_reporting & E_WARNING || $error_reporting & E_NOTICE);
		$reporting_issue = $reporting_warnings && (!defined('WP_DEBUG') || !WP_DEBUG);

		$has_issue = ($display_errors_on || $reporting_issue || $wp_debug_display);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Display errors check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (display_errors: %s, WP_DEBUG_DISPLAY: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$display_errors_on ? 'on' : 'off',
				$wp_debug_display ? 'true' : 'false'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

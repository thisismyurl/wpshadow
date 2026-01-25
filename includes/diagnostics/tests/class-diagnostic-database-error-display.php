<?php

declare(strict_types=1);
/**
 * Database Error Display Diagnostic
 *
 * Philosophy: Information disclosure - hide database errors
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if database errors are displayed to visitors.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Database_Error_Display extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;

		// Check if show_errors is enabled
		if ( $wpdb->show_errors ) {
			return array(
				'id'            => 'database-error-display',
				'title'         => 'Database Errors Displayed to Public',
				'description'   => 'Database errors are being displayed to visitors, potentially revealing database structure, table names, and credentials. Disable WP_DEBUG_DISPLAY in production.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/hide-database-errors/',
				'training_link' => 'https://wpshadow.com/training/database-security/',
				'auto_fixable'  => true,
				'threat_level'  => 70,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Database Error Display
	 * Slug: -database-error-display
	 * File: class-diagnostic-database-error-display.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Database Error Display
	 * Slug: -database-error-display
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
	public static function test_live__database_error_display(): array {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return array(
				'passed'  => false,
				'message' => 'Cannot access $wpdb to verify database error display state',
			);
		}

		$show_errors_enabled = (bool) $wpdb->show_errors;

		// Call diagnostic check
		$diagnostic_result = self::check();

		// Determine expected state
		$should_find_issue      = $show_errors_enabled;
		$diagnostic_found_issue = ( null !== $diagnostic_result );

		// Compare expected vs actual diagnostic result
		$test_passes = ( $should_find_issue === $diagnostic_found_issue );

		$message = sprintf(
			'wpdb show_errors: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$show_errors_enabled ? 'ENABLED' : 'DISABLED',
			$should_find_issue ? 'FIND' : 'NOT find',
			$diagnostic_found_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

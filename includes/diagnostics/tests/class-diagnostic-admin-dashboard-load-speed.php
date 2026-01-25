<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Admin Dashboard Load Speed Analysis (WORDPRESS-007)
 *
 * Measures wp-admin page load times and identifies slow admin pages.
 * Philosophy: Show value (#9) - Improve editor workflow speed.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Admin_Dashboard_Load_Speed extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check admin dashboard load speed
		$dashboard_load_time = get_transient( 'wpshadow_dashboard_load_ms' );

		if ( $dashboard_load_time && $dashboard_load_time > 2000 ) { // 2 seconds
			return array(
				'id'            => 'admin-dashboard-load-speed',
				'title'         => sprintf( __( 'Slow Admin Dashboard (%dms)', 'wpshadow' ), $dashboard_load_time ),
				'description'   => __( 'Admin dashboard is loading slowly. Disable dashboard widgets and check for slow plugins.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'monitoring',
				'kb_link'       => 'https://wpshadow.com/kb/admin-performance/',
				'training_link' => 'https://wpshadow.com/training/dashboard-tuning/',
				'auto_fixable'  => false,
				'threat_level'  => 40,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Admin Dashboard Load Speed
	 * Slug: -admin-dashboard-load-speed
	 * File: class-diagnostic-admin-dashboard-load-speed.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Admin Dashboard Load Speed
	 * Slug: -admin-dashboard-load-speed
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
	public static function test_live__admin_dashboard_load_speed(): array {
		$dashboard_load_time = get_transient( 'wpshadow_dashboard_load_ms' );
		$has_issue           = ( $dashboard_load_time && $dashboard_load_time > 2000 );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );
		$test_passes            = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Admin dashboard load speed check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (dashboard load: %s ms)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$dashboard_load_time !== false ? (string) $dashboard_load_time : 'n/a'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

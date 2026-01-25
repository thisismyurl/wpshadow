<?php

declare(strict_types=1);
/**
 * Automated Security Updates Diagnostic
 *
 * Philosophy: Security best practice - ensure timely patches
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if automatic security updates are enabled.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Automated_Updates extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if automatic updates are disabled
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			return array(
				'id'            => 'automated-updates',
				'title'         => 'Automatic Security Updates Disabled',
				'description'   => 'Automatic security updates are disabled. Enable them to ensure critical patches are applied promptly.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/enable-automatic-security-updates/',
				'training_link' => 'https://wpshadow.com/training/automated-updates/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Automated Updates
	 * Slug: -automated-updates
	 * File: class-diagnostic-automated-updates.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Automated Updates
	 * Slug: -automated-updates
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
	public static function test_live__automated_updates(): array {
		$updates_disabled = ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED );
		$has_issue        = $updates_disabled;

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Automated updates check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (AUTOMATIC_UPDATER_DISABLED: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$updates_disabled ? 'true' : 'false'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

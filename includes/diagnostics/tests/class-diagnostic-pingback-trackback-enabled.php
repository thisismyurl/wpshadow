<?php

declare(strict_types=1);
/**
 * Pingback/Trackback Enabled Diagnostic
 *
 * Philosophy: Legacy features - disable unnecessary endpoints
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if pingback/trackback is enabled.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Pingback_Trackback_Enabled extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( get_option( 'default_ping_status' ) === 'open' ) {
			return array(
				'id'            => 'pingback-trackback-enabled',
				'title'         => 'Pingback/Trackback Enabled',
				'description'   => 'Pingbacks/Trackbacks are old features rarely used. They are exploited for SSRF attacks and amplification attacks. Disable: Settings > Discussion > disable pingbacks.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-pingbacks/',
				'training_link' => 'https://wpshadow.com/training/legacy-features/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pingback Trackback Enabled
	 * Slug: -pingback-trackback-enabled
	 * File: class-diagnostic-pingback-trackback-enabled.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Pingback Trackback Enabled
	 * Slug: -pingback-trackback-enabled
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
	public static function test_live__pingback_trackback_enabled(): array {
		$ping_status = get_option( 'default_ping_status' );
		$has_issue   = ( $ping_status === 'open' );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Pingback/trackback check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (default_ping_status: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$ping_status
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

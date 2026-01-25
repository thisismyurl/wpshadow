<?php

declare(strict_types=1);
/**
 * Favicon / Site Icon Diagnostic
 *
 * Philosophy: Small UX trust signal; educates on branding consistency.
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if a site icon (favicon) is set.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Favicon extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
			return null; // Favicon set
		}

		return array(
			'id'           => 'favicon',
			'title'        => 'No Site Icon (Favicon) Set',
			'description'  => 'Adding a site icon improves brand trust and recognition in browser tabs, bookmarks, and mobile devices.',
			'severity'     => 'low',
			'category'     => 'design',
			'kb_link'      => 'https://wpshadow.com/kb/add-wordpress-site-icon/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=favicon',
			'auto_fixable' => false,
			'threat_level' => 15,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Favicon
	 * Slug: -favicon
	 * File: class-diagnostic-favicon.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Favicon
	 * Slug: -favicon
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
	public static function test_live__favicon(): array {
		$site_icon_id = get_option( 'site_icon' );
		$has_issue    = empty( $site_icon_id );

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Favicon check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (site_icon: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$site_icon_id ? 'set' : 'empty'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

<?php

declare(strict_types=1);
/**
 * Site Tagline Diagnostic
 *
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if site tagline/description is set.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Tagline extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		if (empty(get_bloginfo('description'))) {
			$is_registered = self::is_site_registered();

			$finding = array(
				'id'            => 'tagline-empty',
				'title'         => 'Site Tagline is Empty',
				'description'   => 'Add a tagline (Settings → General) to improve SEO and help visitors understand your site quickly.' . (! $is_registered ? ' 💡 Register with WPShadow and get AI-powered suggestions for the perfect tagline!' : ''),				'kb_link'       => 'https://wpshadow.com/kb/write-an-effective-site-tagline/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=tagline',
				'modal_trigger' => 'wpshadow-tagline-modal',
				'action_text'   => 'Add Tagline',
				'auto_fixable'  => false,
				'threat_level'  => 20,
			);

			// Only show AI button for unregistered sites
			if (! $is_registered) {
				$finding['secondary_action_link'] = 'https://wpshadow.com/register/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=tagline';
				$finding['secondary_action_text'] = 'Get AI Suggestions';
			}

			return $finding;
		}

		return null;
	}

	/**
	 * Check if site is registered with WPShadow.
	 *
	 * @return bool True if site has registered (indicated by email consent).
	 */
	private static function is_site_registered()
	{
		$consent = get_option('wpshadow_email_consent', false);
		return ! empty($consent);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Tagline
	 * Slug: -tagline
	 * File: class-diagnostic-tagline.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Tagline
	 * Slug: -tagline
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
	public static function test_live__tagline(): array
	{
		$description = get_bloginfo('description');
		$has_issue = empty($description);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Tagline check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (tagline: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				empty($description) ? 'empty' : 'set'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

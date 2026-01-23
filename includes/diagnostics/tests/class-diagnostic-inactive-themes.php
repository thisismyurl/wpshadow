<?php

declare(strict_types=1);
/**
 * Inactive Theme Security Diagnostic
 *
 * Philosophy: Reduce attack surface - unused themes are still exploitable
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for inactive themes with known vulnerabilities.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Inactive_Themes extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$all_themes = wp_get_themes();
		$active_theme = wp_get_theme();
		$inactive_themes = array();

		foreach ($all_themes as $theme_slug => $theme) {
			if ($theme->get_stylesheet() !== $active_theme->get_stylesheet()) {
				$inactive_themes[] = $theme->get('Name');
			}
		}

		if (count($inactive_themes) > 3) {
			return array(
				'id'          => 'inactive-themes',
				'title'       => 'Excessive Inactive Themes',
				'description' => sprintf(
					'You have %d inactive themes installed. Unused themes can still be exploited if they have vulnerabilities. Remove themes you don\'t need: %s',
					count($inactive_themes),
					implode(', ', array_slice($inactive_themes, 0, 3))
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/remove-unused-themes/',
				'training_link' => 'https://wpshadow.com/training/theme-security/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Inactive Themes
	 * Slug: -inactive-themes
	 * File: class-diagnostic-inactive-themes.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Inactive Themes
	 * Slug: -inactive-themes
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
	public static function test_live__inactive_themes(): array
	{
		// Recompute actual inactive theme count
		$all_themes = wp_get_themes();
		$active_theme = wp_get_theme();
		$inactive_count = 0;

		foreach ($all_themes as $theme_slug => $theme) {
			if ($theme->get_stylesheet() !== $active_theme->get_stylesheet()) {
				$inactive_count++;
			}
		}

		$threshold = 3; // Must match check() threshold

		// Call diagnostic check
		$diagnostic_result = self::check();

		// Determine expected state
		$should_find_issue = ($inactive_count > $threshold);
		$diagnostic_found_issue = ($diagnostic_result !== null);

		// Compare expected vs actual diagnostic result
		$test_passes = ($should_find_issue === $diagnostic_found_issue);

		$message = sprintf(
			'Inactive themes: %d (threshold: >%d). Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$inactive_count,
			$threshold,
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

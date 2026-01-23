<?php

declare(strict_types=1);
/**
 * Outdated Plugins Diagnostic
 *
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for outdated plugins.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Outdated_Plugins extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$outdated = self::get_outdated_plugins_count();

		if ($outdated > 0) {
			return array(
				'id'           => 'outdated-plugins',
				'title'        => "You Have {$outdated} Outdated Plugin" . ($outdated !== 1 ? 's' : ''),
				'description'  => 'Outdated plugins can cause security vulnerabilities and conflicts. Update them as soon as possible.',
				'color'        => '#f44336',
				'bg_color'     => '#ffebee',
				'kb_link'      => 'https://wpshadow.com/kb/how-to-safely-update-plugins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-updates',
				'action_link'  => admin_url('plugins.php'),
				'action_text'  => 'Update Plugins',
				'auto_fixable' => true,
				'threat_level' => 80,
			);
		}

		return null;
	}

	/**
	 * Count outdated plugins.
	 *
	 * @return int Number of plugins with available updates.
	 */
	private static function get_outdated_plugins_count()
	{
		$updates = get_site_transient('update_plugins');
		return ! empty($updates->response) ? count($updates->response) : 0;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Outdated Plugins
	 * Slug: -outdated-plugins
	 * File: class-diagnostic-outdated-plugins.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Outdated Plugins
	 * Slug: -outdated-plugins
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
	public static function test_live__outdated_plugins(): array
	{
		$updates = get_site_transient('update_plugins');
		$outdated_count = !empty($updates->response) ? count($updates->response) : 0;
		$has_issue = ($outdated_count > 0);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Outdated plugins check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (outdated count: %d)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$outdated_count
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

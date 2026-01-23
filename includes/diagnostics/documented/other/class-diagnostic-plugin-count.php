<?php
declare(strict_types=1);
/**
 * Plugin Count Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for excessive plugin count.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Plugin_Count extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$plugins = get_plugins();
		$count   = count( $plugins );

		if ( $count > 50 ) {
			return array(
				'id'           => 'plugin-count-high',
				'title'        => "High Plugin Count ({$count})",
				'description'  => 'You have many plugins active. Consider auditing for unused ones—each adds overhead.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/audit-and-optimize-your-wordpress-plugins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-optimization',
				'auto_fixable' => false,
				'threat_level' => 40,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Plugin Count
	 * Slug: -plugin-count
	 * File: class-diagnostic-plugin-count.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Plugin Count
	 * Slug: -plugin-count
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
	public static function test_live__plugin_count(): array {
		$plugins = get_plugins();
		$count = count($plugins);
		$has_issue = ($count > 50);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Plugin count check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (plugin count: %d)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$count
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}

}

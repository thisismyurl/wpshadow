<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Premium Plugin Compatibility
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Premium_Plugin_Conflicts extends Diagnostic_Base
{
	protected static $slug        = 'premium-plugin-conflicts';
	protected static $title       = 'Premium Plugin Compatibility';
	protected static $description = 'Detects conflicts with common premium plugins.';

	public static function check(): ?array
	{
		// Known plugin conflict pairs (simplified detection)
		$conflict_pairs = array(
			array('jetpack/jetpack.php', 'wp-rocket/wp-rocket.php'),
			array('wordfence/wordfence.php', 'ithemes-security-pro/ithemes-security-pro.php'),
		);

		$conflicts_found = array();
		foreach ($conflict_pairs as $pair) {
			if (is_plugin_active($pair[0]) && is_plugin_active($pair[1])) {
				$conflicts_found[] = basename(dirname($pair[0])) . ' + ' . basename(dirname($pair[1]));
			}
		}

		if (empty($conflicts_found)) {
			return null; // Pass - no known conflicts
		}

		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'Potential conflicts detected: ' . implode(', ', $conflicts_found),
			'color'         => '#ff9800',
			'bg_color'      => '#fff3e0',
			'kb_link'       => 'https://wpshadow.com/kb/premium-plugin-conflicts/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=premium-plugin-conflicts',
			'training_link' => 'https://wpshadow.com/training/premium-plugin-conflicts/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Compatibility',
			'priority'      => 2,
		);
	}




	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Premium Plugin Compatibility
	 * Slug: premium-plugin-conflicts
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Detects conflicts with common premium plugins.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_premium_plugin_conflicts(): array
	{
		$result = self::check();

		$conflict_pairs = array(
			array('jetpack/jetpack.php', 'wp-rocket/wp-rocket.php'),
			array('wordfence/wordfence.php', 'ithemes-security-pro/ithemes-security-pro.php'),
		);

		$has_conflict = false;
		foreach ($conflict_pairs as $pair) {
			if (is_plugin_active($pair[0]) && is_plugin_active($pair[1])) {
				$has_conflict = true;
				break;
			}
		}

		$diagnostic_found_issue = !is_null($result);
		$test_passes = ($has_conflict === $diagnostic_found_issue);

		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Plugin conflict check matches site state' :
				"Mismatch: expected " . ($has_conflict ? 'issue' : 'no issue') . " but got " .
				($diagnostic_found_issue ? 'issue' : 'pass'),
		);
	}
}

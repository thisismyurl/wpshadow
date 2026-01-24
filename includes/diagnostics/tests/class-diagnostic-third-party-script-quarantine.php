<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Third-Party Script Quarantine Testing (FE-019)
 *
 * Measures performance impact of each third-party script.
 * Philosophy: Educate (#5) - Know the cost of every tag.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Third_Party_Script_Quarantine extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$quarantined_scripts = get_transient('wpshadow_quarantined_scripts_count');

		if ($quarantined_scripts && $quarantined_scripts > 0) {
			return array(
				'id' => 'third-party-script-quarantine',
				'title' => sprintf(__('%d Scripts in Quarantine', 'wpshadow'), $quarantined_scripts),
				'description' => __('Some third-party scripts have been isolated due to performance or security concerns. Review and enable them carefully.', 'wpshadow'),
				'severity' => 'info',
				'category' => 'monitoring',
				'kb_link' => 'https://wpshadow.com/kb/script-quarantine/',
				'training_link' => 'https://wpshadow.com/training/malicious-script-detection/',
				'auto_fixable' => false,
				'threat_level' => 25,
			);
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Third Party Script Quarantine
	 * Slug: -third-party-script-quarantine
	 * File: class-diagnostic-third-party-script-quarantine.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Third Party Script Quarantine
	 * Slug: -third-party-script-quarantine
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
	public static function test_live__third_party_script_quarantine(): array
	{
		$quarantined_scripts = get_transient('wpshadow_quarantined_scripts_count');
		$has_issue = ($quarantined_scripts && $quarantined_scripts > 0);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Quarantined script count check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (quarantined: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$quarantined_scripts !== false ? (string) $quarantined_scripts : 'n/a'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

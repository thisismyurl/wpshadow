<?php

declare(strict_types=1);
/**
 * File Editor Hardening Diagnostic
 *
 * Philosophy: Security best practice; educates on reducing attack surface.
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if DISALLOW_FILE_EDIT is enabled to block theme/plugin editor.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Disallow_File_Edit extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) {
			return null; // Already hardened
		}

		return array(
			'id'           => 'disallow-file-edit',
			'title'        => 'Theme/Plugin File Editor Enabled',
			'description'  => 'Built-in file editor is enabled. Disable it to reduce risk of code tampering.',
			'severity'     => 'medium',
			'category'     => 'security',
			'kb_link'      => 'https://wpshadow.com/kb/disable-wordpress-file-editor/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=file-editor',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Disallow File Edit
	 * Slug: -disallow-file-edit
	 * File: class-diagnostic-disallow-file-edit.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Disallow File Edit
	 * Slug: -disallow-file-edit
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
	public static function test_live__disallow_file_edit(): array
	{
		$is_disabled = (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT);
		$has_issue = !$is_disabled;

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'File editor check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (DISALLOW_FILE_EDIT: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$is_disabled ? 'true' : 'false'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

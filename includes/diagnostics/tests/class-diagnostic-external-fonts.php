<?php

declare(strict_types=1);
/**
 * External Fonts Diagnostic
 *
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Detect usage of Google-hosted fonts that could be inlined or removed.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_External_Fonts extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$external_handles = self::detect_external_font_handles();
		$blocked          = (bool) get_option('wpshadow_block_external_fonts', false);

		if (empty($external_handles) || $blocked) {
			return null;
		}

		$list = implode(', ', $external_handles);

		return array(
			'id'           => 'external-fonts-loading',
			'title'        => 'External Fonts Loaded (Google)',
			'description'  => 'These styles load Google Fonts: ' . $list . '. Consider switching to a system stack to improve privacy and performance.',			'kb_link'      => 'https://wpshadow.com/kb/remove-google-fonts/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=external-fonts',
			'auto_fixable' => true,
			'threat_level' => 30,
		);
	}

	private static function detect_external_font_handles()
	{
		global $wp_styles;
		if (! isset($wp_styles) || empty($wp_styles->queue)) {
			return array();
		}

		$handles = array();
		foreach ($wp_styles->queue as $handle) {
			if (! isset($wp_styles->registered[$handle])) {
				continue;
			}
			$src = $wp_styles->registered[$handle]->src;
			if (is_string($src) && self::is_google_font_src($src)) {
				$handles[] = $handle;
			}
		}

		return $handles;
	}

	private static function is_google_font_src($src)
	{
		return (false !== stripos($src, 'fonts.googleapis.com') || false !== stripos($src, 'fonts.gstatic.com'));
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: External Fonts
	 * Slug: -external-fonts
	 * File: class-diagnostic-external-fonts.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: External Fonts
	 * Slug: -external-fonts
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
	public static function test_live__external_fonts(): array
	{
		$blocked = (bool) get_option('wpshadow_block_external_fonts', false);
		$handles = self::detect_external_font_handles();
		$has_issue = (! $blocked && ! empty($handles));

		$result = self::check();
		$diagnostic_found_issue = is_array($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'External fonts check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (blocked: %s, handles: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$blocked ? 'yes' : 'no',
				empty($handles) ? 'none' : implode(', ', $handles)
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

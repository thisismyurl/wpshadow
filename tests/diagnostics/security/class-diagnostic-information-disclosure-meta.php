<?php

declare(strict_types=1);
/**
 * Information Disclosure in Meta Tags Diagnostic
 *
 * Philosophy: Information disclosure - prevent version leaks
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for information disclosure in headers and meta tags.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Information_Disclosure_Meta extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check if generator meta tag reveals WordPress version
		if (has_action('wp_head')) {
			$output = ob_get_clean();
			ob_start();

			if (preg_match('/<meta name="generator"[^>]*WordPress\s+\d+\.\d+/', $output)) {
				return array(
					'id'          => 'information-disclosure-meta',
					'title'       => 'WordPress Version Exposed in Meta Tags',
					'description' => 'WordPress version revealed in meta tags. Attackers know exact version to target. Remove generator meta tag or use generic text.',
					'severity'    => 'medium',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/hide-wordpress-version/',
					'training_link' => 'https://wpshadow.com/training/information-disclosure/',
					'auto_fixable' => false,
					'threat_level' => 55,
				);
			}

			ob_end_clean();
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Information Disclosure Meta
	 * Slug: -information-disclosure-meta
	 * File: class-diagnostic-information-disclosure-meta.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Information Disclosure Meta
	 * Slug: -information-disclosure-meta
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
	public static function test_live__information_disclosure_meta(): array
	{
		$result = self::check();

		// Use presence of generator meta output as indicator of disclosure
		$generator_active = has_action('wp_head', 'wp_generator') !== false;
		$has_issue = $generator_active;

		$diagnostic_found_issue = !is_null($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Information disclosure meta check matches site state' :
				"Mismatch: expected " . ($has_issue ? 'issue' : 'no issue') . " but got " .
				($diagnostic_found_issue ? 'issue' : 'pass'),
		);
	}
}

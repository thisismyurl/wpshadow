<?php

declare(strict_types=1);
/**
 * WP Generator Tag Diagnostic
 *
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Check if WordPress version is exposed in meta generator tag.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_WP_Generator extends Diagnostic_Base
{

	protected static $slug        = 'wp-generator';
	protected static $title       = 'WordPress Version Exposed';
	protected static $description = 'The WordPress version is visible in the HTML head via the generator meta tag.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check if treatment is already applied
		$disabled = get_option('wpshadow_wp_generator_disabled', false);

		if ($disabled) {
			return null;
		}

		// Check if generator tag is enabled
		$has_generator = has_action('wp_head', 'wp_generator') !== false;

		if (! $has_generator) {
			return null;
		}

		global $wp_version;

		return array(
			'id'          => 'wp-generator',
			'title'       => 'WordPress Version Exposed',
			'description' => 'Your site outputs <meta name="generator" content="WordPress ' . esc_html($wp_version) . '"> in the head. This tells attackers exactly which version you\'re running, making it easier to find exploits.',
			'severity'    => 'warning',
			'category'    => 'security',
			'impact'      => 'Exposes WordPress version to attackers',
			'fix_time'    => '1 second',
			'kb_article'  => 'wp-generator',
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: WordPress Version Exposed
	 * Slug: wp-generator
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: The WordPress version is visible in the HTML head via the generator meta tag.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wp_generator(): array
	{
		$disabled = (bool) get_option('wpshadow_wp_generator_disabled', false);
		$has_generator = (has_action('wp_head', 'wp_generator') !== false);

		// Issue exists if: NOT disabled AND has_generator action
		$has_issue = (!$disabled && $has_generator);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'WP generator check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (disabled: %s, has_generator: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$disabled ? 'yes' : 'no',
				$has_generator ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

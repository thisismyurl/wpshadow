<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

class Diagnostic_Timezone extends Diagnostic_Base
{

	protected static $slug        = 'timezone';
	protected static $title       = 'Timezone Configuration';
	protected static $description = 'Checks if timezone is properly configured with a named timezone instead of UTC offset.';

	public static function check(): ?array
	{
		$timezone_string = get_option('timezone_string');
		$gmt_offset      = get_option('gmt_offset');

		if (empty($timezone_string) && (empty($gmt_offset) || '0' === $gmt_offset)) {
			return null;
		}

		if (empty($timezone_string) && ! empty($gmt_offset) && '0' !== $gmt_offset) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__('Timezone is configured using UTC offset (%s) instead of a named timezone. This can cause issues with scheduled posts, backups, and cron tasks. Use a city-based timezone like "America/New_York" for proper DST handling.', 'wpshadow'),
					$gmt_offset > 0 ? "+{$gmt_offset}" : $gmt_offset
				),
				'category'     => 'settings',
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'timestamp'    => current_time('mysql'),
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Timezone Configuration
	 * Slug: timezone
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if timezone is properly configured with a named timezone instead of UTC offset.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_timezone(): array
	{
		$timezone_string = get_option('timezone_string');
		$gmt_offset = get_option('gmt_offset');

		// Issue exists if: empty timezone_string AND non-zero gmt_offset
		$has_issue = (empty($timezone_string) && !empty($gmt_offset) && '0' !== $gmt_offset);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Timezone check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (timezone_string: %s, gmt_offset: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				empty($timezone_string) ? 'empty' : $timezone_string,
				$gmt_offset
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

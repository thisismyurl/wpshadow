<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

class Diagnostic_Embed_Disable extends Diagnostic_Base
{

	protected static $slug        = 'embed-disable';
	protected static $title       = 'WordPress Embed Scripts';
	protected static $description = 'Checks if WordPress embed scripts are loaded but not being used.';

	public static function check(): ?array
	{
		if (get_option('wpshadow_embed_disable_enabled', false)) {
			return null;
		}

		if (! wp_script_is('wp-embed', 'enqueued') && ! wp_script_is('wp-embed', 'registered')) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __('WordPress embed scripts (wp-embed.js) are loaded but may not be needed. Disabling saves bandwidth and improves performance if you don\'t use embed features.', 'wpshadow'),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => true,
			'timestamp'    => current_time('mysql'),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: WordPress Embed Scripts
	 * Slug: embed-disable
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if WordPress embed scripts are loaded but not being used.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_embed_disable(): array
	{
		$disabled = (bool) get_option('wpshadow_embed_disable_enabled', false);
		$embed_enqueued = wp_script_is('wp-embed', 'enqueued');
		$embed_registered = wp_script_is('wp-embed', 'registered');

		// Issue exists if: NOT disabled AND (enqueued OR registered)
		$has_issue = (!$disabled && ($embed_enqueued || $embed_registered));

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Embed disable check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (disabled: %s, enqueued: %s, registered: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$disabled ? 'yes' : 'no',
				$embed_enqueued ? 'yes' : 'no',
				$embed_registered ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}

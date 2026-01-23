<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_WordPress_Version;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: WordPress Version
 *
 * Validates that the diagnostic correctly identifies outdated WordPress versions.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Ensure WordPress is current for security
 */
class Test_System_WordPress_Version extends Diagnostic_WordPress_Version
{

	/**
	 * Live test for WordPress version diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_wordpress_version(): array
	{
		$result = self::check();
		global $wp_version;

		// Test 1: Check if result matches current WordPress version
		if (version_compare($wp_version, '6.4', '<')) {
			// Should return an issue (update available)
			if (is_null($result)) {
				return array(
					'passed' => false,
					'message' => "WordPress {$wp_version} is < 6.4, but check() returned null instead of issue.",
				);
			}
			if (! isset($result['threat_level']) || $result['threat_level'] < 50) {
				return array(
					'passed' => false,
					'message' => 'WordPress version issue detected but threat_level is incorrect.',
				);
			}
		} else {
			// WordPress is current
			if (! is_null($result)) {
				return array(
					'passed' => false,
					'message' => "WordPress {$wp_version} is current, but check() returned: " . wp_json_encode($result),
				);
			}
		}

		// Test 2: Verify result structure when issue is found
		if (! is_null($result)) {
			$required_keys = array('id', 'title', 'description', 'threat_level');
			foreach ($required_keys as $key) {
				if (! isset($result[$key])) {
					return array(
						'passed' => false,
						'message' => "Result missing required key: {$key}",
					);
				}
			}
		}

		// All tests passed
		return array(
			'passed' => true,
			'message' => "WordPress version {$wp_version} check passed. Expected behavior matches actual result.",
		);
	}
}

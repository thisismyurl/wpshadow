<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_PHP_Version_Compatible;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: PHP Version Compatibility
 *
 * Validates that the diagnostic correctly identifies outdated PHP versions.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Ensure PHP compatibility for security
 */
class Test_System_PHP_Version extends Diagnostic_PHP_Version_Compatible
{

	/**
	 * Live test for PHP version diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_php_version(): array
	{
		$result = self::check();
		$php_version = phpversion();

		// Test 1: Check if result matches current PHP version
		if (version_compare($php_version, '7.4', '<')) {
			// Should return an issue (critical)
			if (is_null($result)) {
				return array(
					'passed' => false,
					'message' => "PHP {$php_version} is < 7.4, but check() returned null instead of issue.",
				);
			}
			if (! isset($result['threat_level']) || $result['threat_level'] < 70) {
				return array(
					'passed' => false,
					'message' => 'PHP version issue detected but threat_level is incorrect.',
				);
			}
		} elseif (version_compare($php_version, '8.0', '<')) {
			// Should return a warning
			if (is_null($result)) {
				return array(
					'passed' => false,
					'message' => "PHP {$php_version} is < 8.0, but check() returned null instead of warning.",
				);
			}
			if (! isset($result['threat_level']) || $result['threat_level'] < 50) {
				return array(
					'passed' => false,
					'message' => 'PHP version warning detected but threat_level is incorrect.',
				);
			}
		} else {
			// PHP 8.0+, should be healthy
			if (! is_null($result)) {
				return array(
					'passed' => false,
					'message' => "PHP {$php_version} is current, but check() returned: " . wp_json_encode($result),
				);
			}
		}

		// All tests passed
		return array(
			'passed' => true,
			'message' => "PHP version {$php_version} check passed. Expected behavior matches actual result.",
		);
	}
}

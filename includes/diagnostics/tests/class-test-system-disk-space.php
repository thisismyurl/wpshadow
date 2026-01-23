<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Disk_Space;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Disk Space Usage
 *
 * Validates that the diagnostic correctly monitors disk space availability.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Prevent disk space issues before they occur
 */
class Test_System_Disk_Space extends Diagnostic_Disk_Space
{

	/**
	 * Live test for disk space diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_disk_space(): array
	{
		$result = self::check();
		$wp_path = ABSPATH;

		// Test 1: Get disk space metrics
		$free_space = disk_free_space($wp_path);
		$total_space = disk_total_space($wp_path);

		if (! $free_space || ! $total_space) {
			return array(
				'passed' => false,
				'message' => 'Unable to retrieve disk space information.',
			);
		}

		// Test 2: Calculate usage percentage
		$usage_percent = (($total_space - $free_space) / $total_space) * 100;

		// Test 3: Check if result matches disk usage
		if ($usage_percent > 80) {
			// Should return an issue (disk nearly full)
			if (is_null($result)) {
				return array(
					'passed' => false,
					'message' => "Disk usage is {$usage_percent}% (> 80%), but check() returned null instead of issue.",
				);
			}
			if (! isset($result['threat_level']) || $result['threat_level'] < 60) {
				return array(
					'passed' => false,
					'message' => 'Disk space issue detected but threat_level is incorrect.',
				);
			}
		} else {
			// Disk space is adequate
			if (! is_null($result)) {
				return array(
					'passed' => false,
					'message' => "Disk usage is {$usage_percent}% (< 80%), but check() returned: " . wp_json_encode($result),
				);
			}
		}

		// Test 4: Verify result structure when issue is found
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
			'message' => "Disk space check passed. Usage: {$usage_percent}%. Expected behavior matches actual result.",
		);
	}
}

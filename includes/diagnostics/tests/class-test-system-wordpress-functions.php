<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: WordPress Functions Availability
 *
 * Validates that critical WordPress functions are available.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Ensure WordPress core functions are accessible
 */
class Test_System_WordPress_Functions extends Diagnostic_Base
{

	/**
	 * Check for required WordPress functions
	 *
	 * @return array|null Issues found or null if all functions available
	 */
	public static function check(): ?array
	{
		$required_functions = array(
			'add_action',
			'add_filter',
			'do_action',
			'apply_filters',
			'get_option',
			'update_option',
			'get_post',
			'get_posts',
			'wp_remote_get',
			'wp_safe_remote_get',
		);

		$missing = array();
		foreach ($required_functions as $function) {
			if (! function_exists($function)) {
				$missing[] = $function;
			}
		}

		if (empty($missing)) {
			return null; // All functions available
		}

		return array(
			'id'           => 'wordpress-functions-missing',
			'title'        => 'Missing WordPress Functions',
			'description'  => 'Critical WordPress functions are missing: ' . implode(', ', $missing),
			'threat_level' => 90,
		);
	}

	/**
	 * Live test for WordPress functions diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_wordpress_functions(): array
	{
		$result = self::check();

		// Test 1: Check each required function manually
		$required_functions = array(
			'add_action',
			'add_filter',
			'do_action',
			'apply_filters',
			'get_option',
			'update_option',
			'get_post',
			'get_posts',
			'wp_remote_get',
			'wp_safe_remote_get',
		);

		$actually_missing = array();
		foreach ($required_functions as $function) {
			if (! function_exists($function)) {
				$actually_missing[] = $function;
			}
		}

		// Test 2: Compare results
		if (! empty($actually_missing)) {
			// Should return an issue
			if (is_null($result)) {
				return array(
					'passed' => false,
					'message' => 'Missing functions: ' . implode(', ', $actually_missing) . ', but check() returned null.',
				);
			}
		} else {
			// All functions available
			if (! is_null($result)) {
				return array(
					'passed' => false,
					'message' => 'All functions available, but check() returned: ' . wp_json_encode($result),
				);
			}
		}

		// All tests passed
		return array(
			'passed' => true,
			'message' => 'WordPress functions check passed. All required functions are available.',
		);
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Plugin_Update_Noise;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Plugin Update Notifications
 *
 * Validates that the diagnostic correctly identifies inactive plugins with pending updates.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Reduce dashboard noise from inactive plugins
 */
class Test_System_Plugin_Update_Noise extends Diagnostic_Plugin_Update_Noise
{

	/**
	 * Live test for plugin update noise diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_plugin_update_noise(): array
	{
		$result = self::check();

		// Test 1: Get inactive plugins and their update status
		if (! function_exists('get_plugins')) {
			return array(
				'passed' => false,
				'message' => 'get_plugins() function not available.',
			);
		}

		$active_plugins = get_option('active_plugins', array());
		if (! is_array($active_plugins)) {
			$active_plugins = array();
		}

		$all_plugins = get_plugins();
		if (empty($all_plugins)) {
			// No plugins, diagnostic should return null
			if (! is_null($result)) {
				return array(
					'passed' => false,
					'message' => 'No plugins found, but check() returned: ' . wp_json_encode($result),
				);
			}
			return array(
				'passed' => true,
				'message' => 'No plugins installed. Diagnostic correctly returns null.',
			);
		}

		// Test 2: Count inactive plugins with updates
		$updates = get_site_transient('update_plugins');
		$inactive_with_updates = 0;

		foreach ($all_plugins as $plugin_file => $plugin_data) {
			if (! in_array($plugin_file, $active_plugins, true)) {
				// Plugin is inactive
				if (! empty($updates->response) && isset($updates->response[$plugin_file])) {
					$inactive_with_updates++;
				}
			}
		}

		// Test 3: Check if result matches expected state
		if ($inactive_with_updates > 0) {
			// Should return an issue
			if (is_null($result)) {
				return array(
					'passed' => false,
					'message' => "Found {$inactive_with_updates} inactive plugins with updates, but check() returned null.",
				);
			}
			if (! isset($result['threat_level'])) {
				return array(
					'passed' => false,
					'message' => 'Result missing threat_level key.',
				);
			}
		} else {
			// No inactive plugins with updates, should return null
			if (! is_null($result)) {
				return array(
					'passed' => false,
					'message' => 'No inactive plugins with updates, but check() returned: ' . wp_json_encode($result),
				);
			}
		}

		// All tests passed
		return array(
			'passed' => true,
			'message' => "Plugin update noise check passed. Found {$inactive_with_updates} inactive plugins with updates.",
		);
	}
}

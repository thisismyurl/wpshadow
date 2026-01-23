<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Theme_Update_Noise;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Theme Update Notifications
 *
 * Validates that the diagnostic correctly identifies inactive themes with pending updates.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Reduce dashboard noise from inactive themes
 */
class Test_System_Theme_Update_Noise extends Diagnostic_Theme_Update_Noise
{

	/**
	 * Live test for theme update noise diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_theme_update_noise(): array
	{
		$result = self::check();

		// Test 1: Get current theme
		if (! function_exists('wp_get_theme')) {
			return array(
				'passed' => false,
				'message' => 'wp_get_theme() function not available.',
			);
		}

		$current_theme = wp_get_theme();
		if (! $current_theme || ! $current_theme->exists()) {
			return array(
				'passed' => false,
				'message' => 'Unable to determine current theme.',
			);
		}

		// Test 2: Check for theme updates
		$updates = get_site_transient('update_themes');
		$inactive_with_updates = 0;

		if (! empty($updates->response) && is_array($updates->response)) {
			// Count inactive themes with updates
			$all_themes = wp_get_themes();
			$current_slug = $current_theme->get_stylesheet();

			foreach ($all_themes as $theme) {
				$theme_slug = $theme->get_stylesheet();
				if ($theme_slug !== $current_slug) {
					// Theme is inactive
					if (isset($updates->response[$theme_slug])) {
						$inactive_with_updates++;
					}
				}
			}
		}

		// Test 3: Check if result matches expected state
		if ($inactive_with_updates > 0) {
			// Should return an issue
			if (is_null($result)) {
				return array(
					'passed' => false,
					'message' => "Found {$inactive_with_updates} inactive themes with updates, but check() returned null.",
				);
			}
			if (! isset($result['threat_level'])) {
				return array(
					'passed' => false,
					'message' => 'Result missing threat_level key.',
				);
			}
		} else {
			// No inactive themes with updates, should return null
			if (! is_null($result)) {
				return array(
					'passed' => false,
					'message' => 'No inactive themes with updates, but check() returned: ' . wp_json_encode($result),
				);
			}
		}

		// All tests passed
		return array(
			'passed' => true,
			'message' => "Theme update noise check passed. Found {$inactive_with_updates} inactive themes with updates.",
		);
	}
}

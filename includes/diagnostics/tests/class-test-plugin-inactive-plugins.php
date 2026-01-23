<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Inactive Plugins
 * Checks for installed but deactivated plugins
 */
class Test_Plugin_Inactive_Plugins extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		if (!function_exists('get_plugins')) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

		$inactive_count = count($plugins) - count($active_plugins);

		if ($inactive_count > 10) {
			return array(
				'id'            => 'plugin-inactive-plugins',
				'title'         => 'Many Inactive Plugins',
				'threat_level'  => 25,
				'description'   => sprintf(
					'%d plugins installed but not active. Delete unnecessary ones.',
					$inactive_count
				),
			);
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_inactive_plugins(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Inactive plugin count is normal' : 'Many inactive plugins found',
		);
	}
}

<?php

/**
 * WPShadow WordPress Diagnostic Test: Inactive Plugins
 *
 * Tests if site has inactive plugins installed (performance/security issue).
 *
 * Testable via: get_plugins(), get_option('active_plugins')
 * Can be requested by Guardian: "test-inactive-plugins", "test-inactive-plugins-list", etc.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    WordPress Configuration
 * @philosophy  #7 Ridiculously Good - Clean, optimized plugin stack
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Inactive Plugins
 *
 * Detects installed but inactive plugins that waste space and require updates.
 *
 * @verified Not yet tested
 */
class Test_Inactive_Plugins extends Diagnostic_Base
{

	protected static $slug = 'inactive-plugins';
	protected static $title = 'Inactive Plugins';
	protected static $description = 'Detects installed but inactive plugins.';

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		$all_plugins = get_plugins();
		$active_plugins = get_option('active_plugins', array());

		$inactive_count = count($all_plugins) - count($active_plugins);

		if ($inactive_count < 3) {
			return null; // Acceptable
		}

		// Calculate space wasted
		$total_size = 0;
		$inactive_list = array();

		foreach ($all_plugins as $plugin_path => $plugin_data) {
			if (! in_array($plugin_path, $active_plugins, true)) {
				$plugin_dir = dirname($plugin_path);
				$plugin_full_path = WP_PLUGIN_DIR . '/' . $plugin_dir;
				$size = self::get_dir_size($plugin_full_path);
				$total_size += $size;
				$inactive_list[] = array(
					'name' => $plugin_data['Name'],
					'version' => $plugin_data['Version'],
					'size' => $size,
				);
			}
		}

		$threat_level = min(50, $inactive_count * 3);

		return array(
			'id'            => static::$slug,
			'title'         => $inactive_count . ' inactive plugins installed',
			'description'   => sprintf(
				'%d inactive plugins use %s of disk space and need updates. Deactivate or delete unused plugins to reduce clutter and security surface.',
				$inactive_count,
				self::format_bytes($total_size)
			)
			'kb_link'       => 'https://wpshadow.com/kb/inactive-plugins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=inactive-plugins',
			'training_link' => 'https://wpshadow.com/training/plugin-management/',
			'auto_fixable'  => false,
			'threat_level'  => $threat_level,
			'module'        => 'WordPress Configuration',
			'priority'      => 4,
			'meta'          => array(
				'inactive_count' => $inactive_count,
				'total_inactive_size' => $total_size,
				'total_size_formatted' => self::format_bytes($total_size),
				'inactive_plugins' => array_slice($inactive_list, 0, 5), // Top 5
			),
		);
	}

	/**
	 * Helper: Get directory size recursively
	 */
	private static function get_dir_size($dir): int
	{
		$size = 0;
		if (is_dir($dir)) {
			foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
				if (is_file($each)) {
					$size += filesize($each);
				} elseif (is_dir($each)) {
					$size += self::get_dir_size($each);
				}
			}
		}
		return $size;
	}

	/**
	 * Helper: Format bytes to human-readable
	 */
	private static function format_bytes($bytes): string
	{
		$units = array('B', 'KB', 'MB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow));

		return round($bytes, 2) . ' ' . $units[$pow];
	}

	/**
	 * Guardian can request: "test-inactive-plugins-count"
	 * Returns count of inactive plugins
	 */
	public static function test_inactive_plugins_count(): array
	{
		$all_plugins = get_plugins();
		$active_plugins = get_option('active_plugins', array());
		$inactive_count = count($all_plugins) - count($active_plugins);
		$passed = $inactive_count < 3;

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ Only {$inactive_count} inactive plugins"
				: "✗ {$inactive_count} inactive plugins (threshold: 3)",
			'data'    => array(
				'inactive_count' => $inactive_count,
				'active_count' => count($active_plugins),
				'total_plugins' => count($all_plugins),
			),
		);
	}

	/**
	 * Guardian can request: "test-inactive-plugins-size"
	 * Returns total size of inactive plugins
	 */
	public static function test_inactive_plugins_size(): array
	{
		$all_plugins = get_plugins();
		$active_plugins = get_option('active_plugins', array());
		$total_size = 0;

		foreach ($all_plugins as $plugin_path => $plugin_data) {
			if (! in_array($plugin_path, $active_plugins, true)) {
				$plugin_dir = dirname($plugin_path);
				$plugin_full_path = WP_PLUGIN_DIR . '/' . $plugin_dir;
				$total_size += self::get_dir_size($plugin_full_path);
			}
		}

		$passed = $total_size < 10485760; // 10 MB

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ Inactive plugins use only " . self::format_bytes($total_size)
				: "✗ Inactive plugins use " . self::format_bytes($total_size) . " (threshold: 10 MB)",
			'data'    => array(
				'total_size' => $total_size,
				'total_size_formatted' => self::format_bytes($total_size),
			),
		);
	}

	/**
	 * Guardian can request: "test-inactive-plugins-list"
	 * Returns detailed list of all inactive plugins
	 */
	public static function test_inactive_plugins_list(): array
	{
		$all_plugins = get_plugins();
		$active_plugins = get_option('active_plugins', array());
		$inactive_list = array();

		foreach ($all_plugins as $plugin_path => $plugin_data) {
			if (! in_array($plugin_path, $active_plugins, true)) {
				$plugin_dir = dirname($plugin_path);
				$plugin_full_path = WP_PLUGIN_DIR . '/' . $plugin_dir;
				$size = self::get_dir_size($plugin_full_path);
				$inactive_list[] = array(
					'plugin' => $plugin_data['Name'],
					'version' => $plugin_data['Version'],
					'author' => $plugin_data['Author'] ?? 'Unknown',
					'size' => self::format_bytes($size),
					'path' => $plugin_path,
				);
			}
		}

		usort($inactive_list, fn($a, $b) => $b['size'] <=> $a['size']);

		return array(
			'passed'  => true,
			'message' => count($inactive_list) . ' inactive plugins found',
			'data'    => array(
				'count' => count($inactive_list),
				'plugins' => $inactive_list,
			),
		);
	}
}

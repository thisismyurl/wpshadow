<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive JavaScript Files in Admin
 *
 * Tests if wp-admin loads too many JavaScript files, which can cause:
 * - Slower parse/execution times
 * - Memory overhead
 * - HTTP request overhead
 *
 * Pattern: Uses WordPress internal $wp_scripts API
 * Context: Requires admin context, uses global $wp_scripts
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #8 Inspire Confidence - Fast admin interface builds trust
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin JavaScript File Count
 *
 * Checks if wp-admin has excessive JavaScript files enqueued (> 30)
 *
 * @verified Not yet tested
 */
class Test_Admin_JS_File_Count extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Only run in admin context
		if (! is_admin()) {
			return null;
		}

		global $wp_scripts;

		// Ensure wp_scripts is initialized
		if (! isset($wp_scripts) || ! is_object($wp_scripts)) {
			return null;
		}

		// Count enqueued scripts
		$enqueued_count = count($wp_scripts->queue ?? array());

		// Get actual enqueued handles for analysis
		$enqueued_handles = $wp_scripts->queue ?? array();

		// Threshold: More than 30 enqueued scripts is excessive
		$threshold = 30;

		if ($enqueued_count <= $threshold) {
			return null; // Pass
		}

		// Categorize scripts
		$plugin_scripts = array();
		$theme_scripts = array();
		$core_scripts = array();
		$external_scripts = array();

		foreach ($enqueued_handles as $handle) {
			if (! isset($wp_scripts->registered[$handle])) {
				continue;
			}

			$src = $wp_scripts->registered[$handle]->src ?? '';

			if (strpos($src, 'wp-includes') !== false || strpos($src, 'wp-admin') !== false) {
				$core_scripts[] = $handle;
			} elseif (strpos($src, 'wp-content/plugins') !== false) {
				$plugin_scripts[] = $handle;
			} elseif (strpos($src, 'wp-content/themes') !== false) {
				$theme_scripts[] = $handle;
			} elseif (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0 || strpos($src, '//') === 0) {
				$external_scripts[] = $handle;
			}
		}

		$plugin_count = count($plugin_scripts);
		$external_count = count($external_scripts);

		return array(
			'id'           => 'admin-js-file-count',
			'title'        => 'Too Many JavaScript Files in Admin Dashboard',
			'description'  => sprintf(
				'WordPress admin is loading %d JavaScript files (%d from plugins, %d external). This slows parse/execution and increases memory usage. Recommended: Under %d files.',
				$enqueued_count,
				$plugin_count,
				$external_count,
				$threshold
			)
			'kb_link'      => 'https://wpshadow.com/kb/admin-js-bloat',
			'training_link' => 'https://wpshadow.com/training/optimize-admin-assets',
			'auto_fixable' => false,
			'threat_level' => 50, // High priority
			'module'       => 'admin-performance',
			'priority'     => 2,
			'meta'         => array(
				'js_count'         => $enqueued_count,
				'plugin_scripts'   => $plugin_count,
				'external_scripts' => $external_count,
				'threshold'        => $threshold,
				'top_culprits'     => array_slice($plugin_scripts, 0, 5), // Top 5 plugin scripts
			),
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin JavaScript File Count',
			'category'    => 'admin-performance',
			'severity'    => 'high',
			'description' => 'Detects excessive JavaScript files loaded in WordPress admin',
		);
	}
}

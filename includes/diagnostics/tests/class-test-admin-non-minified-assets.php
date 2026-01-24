<?php

/**
 * WPShadow Admin Diagnostic Test: Non-Minified Assets in Admin
 *
 * Tests if wp-admin loads non-minified JavaScript or CSS files, which causes:
 * - Larger file sizes (slower download)
 * - Increased bandwidth usage
 * - Unprofessional appearance (dev assets in production)
 * - Slower parse times
 *
 * Pattern: Checks enqueued assets for minify=false or non-minified filenames
 * Context: Requires admin context, uses $wp_styles and $wp_scripts
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #7 Ridiculously Good - Production should use optimized assets
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Non-Minified Assets
 *
 * Checks for non-minified CSS/JS files in admin
 *
 * @verified Not yet tested
 */
class Test_Admin_Non_Minified_Assets extends Diagnostic_Base
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

		global $wp_styles, $wp_scripts;

		$non_minified_assets = array();

		// Check CSS files
		if (isset($wp_styles) && is_object($wp_styles)) {
			foreach ($wp_styles->queue ?? array() as $handle) {
				if (! isset($wp_styles->registered[$handle])) {
					continue;
				}

				$src = $wp_styles->registered[$handle]->src ?? '';

				if ($this->is_non_minified($src)) {
					$non_minified_assets[] = array(
						'type'   => 'css',
						'handle' => $handle,
						'src'    => $src,
					);
				}
			}
		}

		// Check JavaScript files
		if (isset($wp_scripts) && is_object($wp_scripts)) {
			foreach ($wp_scripts->queue ?? array() as $handle) {
				if (! isset($wp_scripts->registered[$handle])) {
					continue;
				}

				$src = $wp_scripts->registered[$handle]->src ?? '';

				if ($this->is_non_minified($src)) {
					$non_minified_assets[] = array(
						'type'   => 'js',
						'handle' => $handle,
						'src'    => $src,
					);
				}
			}
		}

		$non_minified_count = count($non_minified_assets);

		// Any non-minified assets in production is a concern
		if ($non_minified_count === 0) {
			return null; // Pass
		}

		// Categorize by source
		$plugin_assets = array_filter($non_minified_assets, function ($asset) {
			return strpos($asset['src'], 'wp-content/plugins') !== false;
		});

		$theme_assets = array_filter($non_minified_assets, function ($asset) {
			return strpos($asset['src'], 'wp-content/themes') !== false;
		});

		return array(
			'id'           => 'admin-non-minified-assets',
			'title'        => 'Non-Minified Assets in Admin',
			'description'  => sprintf(
				'WordPress admin is loading %d non-minified assets (%d from plugins, %d from theme). Non-minified files are 2-3x larger and indicate development mode in production. Recommended: All production assets should be minified.',
				$non_minified_count,
				count($plugin_assets),
				count($theme_assets)
			)
			'kb_link'      => 'https://wpshadow.com/kb/minify-assets',
			'training_link' => 'https://wpshadow.com/training/optimize-production-assets',
			'auto_fixable' => false,
			'threat_level' => 35, // Medium priority - performance issue
			'module'       => 'admin-performance',
			'priority'     => 11,
			'meta'         => array(
				'total_count'     => $non_minified_count,
				'plugin_count'    => count($plugin_assets),
				'theme_count'     => count($theme_assets),
				'sample_assets'   => array_slice($non_minified_assets, 0, 5), // First 5 assets
			),
		);
	}

	/**
	 * Check if asset URL indicates non-minified file
	 *
	 * @param string $src Asset source URL
	 * @return bool True if non-minified
	 */
	private function is_non_minified(string $src): bool
	{
		if (empty($src)) {
			return false;
		}

		// Check for explicit minify=false parameter
		if (strpos($src, 'minify=false') !== false) {
			return true;
		}

		// Parse filename from URL
		$path = wp_parse_url($src, PHP_URL_PATH);
		if (empty($path)) {
			return false;
		}

		$filename = basename($path);

		// Check if file has .min.css or .min.js extension
		if (preg_match('/\.min\.(css|js)$/i', $filename)) {
			return false; // Has .min, so it's minified
		}

		// Check if it's a CSS or JS file without .min
		if (preg_match('/\.(css|js)$/i', $filename)) {
			// It's a CSS/JS file but doesn't have .min
			// Exclude WordPress core files (they use load-scripts.php or are pre-minified)
			if (strpos($src, 'wp-includes') !== false || strpos($src, 'wp-admin') !== false) {
				return false; // Core files are assumed OK
			}

			return true; // Non-minified plugin/theme file
		}

		return false;
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin Non-Minified Assets',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects non-minified CSS/JS files in production admin',
		);
	}
}

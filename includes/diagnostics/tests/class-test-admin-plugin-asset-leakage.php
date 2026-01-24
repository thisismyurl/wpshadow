<?php

/**
 * WPShadow Admin Diagnostic Test: Plugin Asset Leakage in Admin
 *
 * Tests if front-end-only plugins are loading assets in wp-admin, which causes:
 * - Wasted bandwidth and memory
 * - Slower admin load times
 * - JavaScript conflicts (front-end code running in admin context)
 * - Poor plugin quality indication
 *
 * Pattern: Detects common front-end-only plugins loading scripts/styles in admin
 * Context: Requires admin context, uses $wp_styles and $wp_scripts
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance & Code Quality
 * @philosophy  #7 Ridiculously Good - Assets should only load where needed
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Plugin Asset Leakage
 *
 * Detects front-end plugins improperly loading assets in admin
 *
 * @verified Not yet tested
 */
class Test_Admin_Plugin_Asset_Leakage extends Diagnostic_Base
{

	/**
	 * Known front-end-only plugin slugs
	 *
	 * These should NEVER load assets in wp-admin
	 *
	 * @var array
	 */
	private $frontend_only_plugins = array(
		'elementor',
		'contact-form-7',
		'mailchimp-for-wp',
		'wpforms',
		'ninja-forms',
		'yoast-seo-amp',
		'amp',
		'woocommerce', // WooCommerce frontend assets
		'woocommerce-gateway',
		'jetpack-blocks',
		'google-analytics',
		'google-site-kit',
		'cookie-notice',
		'gdpr-cookie-consent',
		'cookie-law-info',
		'popup-maker',
		'popup-builder',
		'slider-revolution',
		'layerslider',
		'social-warfare',
		'addtoany',
		'sumome',
	);

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

		$leaked_assets = array();

		// Check CSS files
		if (isset($wp_styles) && is_object($wp_styles)) {
			foreach ($wp_styles->queue ?? array() as $handle) {
				if (! isset($wp_styles->registered[$handle])) {
					continue;
				}

				$src = $wp_styles->registered[$handle]->src ?? '';
				$plugin_slug = $this->detect_frontend_plugin($src);

				if ($plugin_slug) {
					$leaked_assets[] = array(
						'type'   => 'css',
						'handle' => $handle,
						'plugin' => $plugin_slug,
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
				$plugin_slug = $this->detect_frontend_plugin($src);

				if ($plugin_slug) {
					$leaked_assets[] = array(
						'type'   => 'js',
						'handle' => $handle,
						'plugin' => $plugin_slug,
						'src'    => $src,
					);
				}
			}
		}

		$leaked_count = count($leaked_assets);

		// Any front-end plugin assets in admin is an issue
		if ($leaked_count === 0) {
			return null; // Pass
		}

		// Count unique plugins
		$unique_plugins = array_unique(array_column($leaked_assets, 'plugin'));
		$plugin_count = count($unique_plugins);

		return array(
			'id'           => 'admin-plugin-asset-leakage',
			'title'        => 'Front-End Plugin Assets Loading in Admin',
			'description'  => sprintf(
				'Detected %d front-end plugin assets loading in wp-admin from %d plugins. These assets should only load on public pages. This indicates poor plugin coding and wastes server resources.',
				$leaked_count,
				$plugin_count
			)
			'kb_link'      => 'https://wpshadow.com/kb/prevent-asset-leakage',
			'training_link' => 'https://wpshadow.com/training/conditional-asset-loading',
			'auto_fixable' => false,
			'threat_level' => 38, // Medium priority - code quality issue
			'module'       => 'admin-performance',
			'priority'     => 12,
			'meta'         => array(
				'leaked_count'     => $leaked_count,
				'plugin_count'     => $plugin_count,
				'culprit_plugins'  => array_values($unique_plugins),
				'sample_assets'    => array_slice($leaked_assets, 0, 5), // First 5 assets
			),
		);
	}

	/**
	 * Detect if asset is from a known front-end-only plugin
	 *
	 * @param string $src Asset source URL
	 * @return string|null Plugin slug if detected, null otherwise
	 */
	private function detect_frontend_plugin(string $src): ?string
	{
		if (empty($src)) {
			return null;
		}

		// Check if it's a plugin asset
		if (strpos($src, 'wp-content/plugins') === false) {
			return null; // Not a plugin
		}

		// Extract plugin slug from path
		// Example: /wp-content/plugins/elementor/assets/css/frontend.css
		preg_match('#/wp-content/plugins/([^/]+)/#', $src, $matches);

		if (empty($matches[1])) {
			return null;
		}

		$plugin_slug = $matches[1];

		// Check if this plugin is in our front-end-only list
		foreach ($this->frontend_only_plugins as $known_slug) {
			if (strpos($plugin_slug, $known_slug) !== false) {
				return $plugin_slug;
			}
		}

		// Also check for common front-end asset indicators in filename
		$filename = basename($src);
		$frontend_indicators = array('frontend', 'public', 'site', 'visitor', 'front-end');

		foreach ($frontend_indicators as $indicator) {
			if (stripos($filename, $indicator) !== false) {
				// This asset has "frontend" in the name but is loading in admin
				return $plugin_slug . ' (detected by filename)';
			}
		}

		return null;
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin Plugin Asset Leakage',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects front-end plugin assets improperly loading in wp-admin',
		);
	}
}

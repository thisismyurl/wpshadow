<?php

/**
 * WPShadow Admin Diagnostic Test: Duplicate Enqueued Assets
 *
 * Tests if wp-admin loads the same asset multiple times, which causes:
 * - Wasted bandwidth downloading duplicate files
 * - Higher memory usage
 * - Potential JavaScript conflicts (same lib twice)
 * - Poor plugin quality indication
 *
 * Pattern: Compares src URLs in $wp_scripts and $wp_styles for duplicates
 * Context: Requires admin context, uses $wp_styles and $wp_scripts
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance & Code Quality
 * @philosophy  #7 Ridiculously Good - Efficient asset loading
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Duplicate Assets
 *
 * Detects duplicate asset URLs in enqueued scripts/styles
 *
 * @verified Not yet tested
 */
class Test_Admin_Duplicate_Assets extends Diagnostic_Base
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

		$duplicates = array();

		// Track src URLs we've seen
		$seen_urls = array();

		// Check JavaScript files
		if (isset($wp_scripts) && is_object($wp_scripts)) {
			foreach ($wp_scripts->queue ?? array() as $handle) {
				if (! isset($wp_scripts->registered[$handle])) {
					continue;
				}

				$src = $wp_scripts->registered[$handle]->src ?? '';

				if (empty($src)) {
					continue;
				}

				// Normalize URL (remove query strings for comparison)
				$normalized = $this->normalize_url($src);

				if (isset($seen_urls[$normalized])) {
					// Duplicate found
					$duplicates[] = array(
						'type'           => 'script',
						'url'            => $normalized,
						'handle_current' => $handle,
						'handle_first'   => $seen_urls[$normalized]['handle'],
						'src_current'    => $src,
						'src_first'      => $seen_urls[$normalized]['src'],
					);
				} else {
					$seen_urls[$normalized] = array(
						'handle' => $handle,
						'src'    => $src,
					);
				}
			}
		}

		// Check CSS files
		if (isset($wp_styles) && is_object($wp_styles)) {
			foreach ($wp_styles->queue ?? array() as $handle) {
				if (! isset($wp_styles->registered[$handle])) {
					continue;
				}

				$src = $wp_styles->registered[$handle]->src ?? '';

				if (empty($src)) {
					continue;
				}

				$normalized = $this->normalize_url($src);

				if (isset($seen_urls[$normalized])) {
					$duplicates[] = array(
						'type'           => 'stylesheet',
						'url'            => $normalized,
						'handle_current' => $handle,
						'handle_first'   => $seen_urls[$normalized]['handle'],
						'src_current'    => $src,
						'src_first'      => $seen_urls[$normalized]['src'],
					);
				} else {
					$seen_urls[$normalized] = array(
						'handle' => $handle,
						'src'    => $src,
					);
				}
			}
		}

		$duplicate_count = count($duplicates);

		// Any duplicates are a problem
		if ($duplicate_count === 0) {
			return null; // Pass
		}

		// Identify common duplicate libraries
		$common_duplicates = array();
		foreach ($duplicates as $dup) {
			$url = $dup['url'];
			if (strpos($url, 'jquery') !== false) {
				$common_duplicates[] = 'jQuery';
			} elseif (strpos($url, 'lodash') !== false || strpos($url, 'underscore') !== false) {
				$common_duplicates[] = 'Lodash/Underscore';
			} elseif (strpos($url, 'bootstrap') !== false) {
				$common_duplicates[] = 'Bootstrap';
			} elseif (strpos($url, 'font-awesome') !== false) {
				$common_duplicates[] = 'Font Awesome';
			}
		}

		return array(
			'id'           => 'admin-duplicate-assets',
			'title'        => 'Duplicate Assets Loaded in Admin',
			'description'  => sprintf(
				'WordPress admin is loading %d duplicate assets. The same files are enqueued multiple times, wasting bandwidth and potentially causing conflicts. Common culprits: %s',
				$duplicate_count,
				! empty($common_duplicates) ? implode(', ', array_unique($common_duplicates)) : 'Multiple plugins'
			),
			'color'        => '#FF4500',
			'bg_color'     => '#FFF4F1',
			'kb_link'      => 'https://wpshadow.com/kb/fix-duplicate-assets',
			'training_link' => 'https://wpshadow.com/training/proper-asset-enqueue',
			'auto_fixable' => false,
			'threat_level' => 40,
			'module'       => 'admin-performance',
			'priority'     => 15,
			'meta'         => array(
				'duplicate_count'    => $duplicate_count,
				'common_duplicates'  => array_unique($common_duplicates),
				'duplicates'         => array_slice($duplicates, 0, 5), // First 5
			),
		);
	}

	/**
	 * Normalize URL for comparison
	 *
	 * Removes query strings and version parameters
	 *
	 * @param string $url URL to normalize
	 * @return string Normalized URL
	 */
	private function normalize_url(string $url): string
	{
		// Remove query string
		$url = strtok($url, '?');

		// Convert to lowercase for case-insensitive comparison
		$url = strtolower($url);

		// Remove protocol for comparison
		$url = preg_replace('#^https?://#', '', $url);

		return $url;
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin Duplicate Assets',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects same asset loaded multiple times',
		);
	}
}

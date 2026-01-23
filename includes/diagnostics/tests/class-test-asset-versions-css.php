<?php

declare(strict_types=1);

/**
 * WPShadow Diagnostic Test: CSS Asset Version Strings
 *
 * Detects CSS enqueues that use version query strings (?ver=), which can be removed
 * to improve caching and reduce cache fragmentation.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2301
 * @category    Performance
 * @philosophy  #7 Ridiculously Good - Efficient caching, #9 Show Value - measure wasted requests
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

class Test_Asset_Versions_CSS extends Diagnostic_Base
{
	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Respect setting that already handles removal
		if (get_option('wpshadow_asset_version_removal_enabled', false)) {
			return null;
		}

		// Only evaluate when assets have been enqueued
		if (! did_action('wp_enqueue_scripts') && ! did_action('admin_enqueue_scripts')) {
			return null;
		}

		global $wp_styles;

		if (! isset($wp_styles) || ! ($wp_styles instanceof \WP_Styles)) {
			return null;
		}

		$versioned = array();

		foreach ($wp_styles->registered as $handle => $style) {
			$src = $style->src ?? '';

			if (! is_string($src) || $src === '') {
				continue;
			}

			if (strpos($src, '?ver=') !== false) {
				$versioned[] = array(
					'handle' => $handle,
					'src'    => $src,
				);
			}
		}

		$versioned_count = count($versioned);

		if ($versioned_count === 0) {
			return null; // Pass
		}

		$examples = array_slice(array_column($versioned, 'handle'), 0, 3);

		return array(
			'id'           => 'asset-versions-css',
			'title'        => 'CSS Asset Version Strings',
			'description'  => sprintf(
				'Found %d CSS files with version query strings (?ver=). Examples: %s',
				$versioned_count,
				implode(', ', $examples)
			),
			'kb_link'      => 'https://wpshadow.com/kb/remove-asset-version-strings',
			'training_link' => 'https://wpshadow.com/training/asset-cache-busting',
			'category'     => 'performance',
			'severity'     => 'low',
			'auto_fixable' => true,
			'threat_level' => 8,
			'priority'     => 14,
			'module'       => 'admin-performance',
			'meta'         => array(
				'versioned_count' => $versioned_count,
				'examples'        => array_slice($versioned, 0, 5),
			),
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Asset Versions (CSS)',
			'category'    => 'performance',
			'severity'    => 'low',
			'description' => 'Detects CSS files using ?ver= cache-busting query strings.',
		);
	}
}

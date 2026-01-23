<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Admin Bar Items
 *
 * Tests if the WordPress admin bar has too many items, which can cause:
 * - Visual clutter
 * - DOM weight
 * - Reduced usability
 * - Mobile responsive issues
 *
 * Pattern: Uses global $wp_admin_bar to count menu items
 * Context: Requires admin context or front-end with admin bar visible
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin UX
 * @philosophy  #8 Inspire Confidence - Clean, focused toolbar
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Bar Item Count
 *
 * Checks if admin bar has excessive top-level items (> 12)
 *
 * @verified Not yet tested
 */
class Test_Admin_Bar_Item_Count extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Only run in admin context or if admin bar is showing
		if (! is_admin() && ! is_admin_bar_showing()) {
			return null;
		}

		global $wp_admin_bar;

		// Ensure admin bar is available
		if (! isset($wp_admin_bar) || ! is_object($wp_admin_bar)) {
			return null;
		}

		// Get all admin bar nodes
		$nodes = $wp_admin_bar->get_nodes();

		if (empty($nodes) || ! is_array($nodes)) {
			return null;
		}

		// Count top-level items (items with no parent)
		$top_level_count = 0;
		$top_level_items = array();
		$plugin_items = 0;
		$core_items = 0;

		foreach ($nodes as $node_id => $node) {
			// Only count top-level items (no parent)
			if (empty($node->parent) || $node->parent === false) {
				$top_level_count++;
				$top_level_items[] = $node->title ?? $node_id;

				// Try to identify plugin vs core items
				// Core items typically start with 'wp-' or are in known list
				$is_core = in_array(
					$node_id,
					array('wp-logo', 'site-name', 'my-account', 'search', 'customize', 'updates', 'comments', 'new-content', 'edit'),
					true
				) || strpos($node_id, 'wp-') === 0;

				if ($is_core) {
					$core_items++;
				} else {
					$plugin_items++;
				}
			}
		}

		// Threshold: More than 12 top-level admin bar items is excessive
		$threshold = 12;

		if ($top_level_count <= $threshold) {
			return null; // Pass
		}

		return array(
			'id'           => 'admin-bar-item-count',
			'title'        => 'Too Many Admin Bar Items',
			'description'  => sprintf(
				'WordPress admin bar has %d top-level items (%d from plugins, %d core). This creates visual clutter and reduces usability, especially on mobile. Recommended: Under %d items.',
				$top_level_count,
				$plugin_items,
				$core_items,
				$threshold
			),
			'color'        => '#FFA500',
			'bg_color'     => '#FFF8F0',
			'kb_link'      => 'https://wpshadow.com/kb/manage-admin-bar',
			'training_link' => 'https://wpshadow.com/training/customize-admin-bar',
			'auto_fixable' => false,
			'threat_level' => 32, // Medium-low priority
			'module'       => 'admin-ux',
			'priority'     => 9,
			'meta'         => array(
				'top_level_count' => $top_level_count,
				'plugin_items'    => $plugin_items,
				'core_items'      => $core_items,
				'threshold'       => $threshold,
				'sample_items'    => array_slice($top_level_items, 0, 10), // First 10 items
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
			'name'        => 'Admin Bar Item Count',
			'category'    => 'admin-ux',
			'severity'    => 'low-medium',
			'description' => 'Detects excessive admin bar items causing clutter',
		);
	}
}

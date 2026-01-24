<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Admin Menu Items
 *
 * Tests if wp-admin has too many menu items, which can cause:
 * - User confusion and cognitive overload
 * - Poor user experience
 * - Visual clutter
 * - Difficulty finding features
 *
 * Pattern: Uses WordPress $menu global to count items
 * Context: Requires admin context, uses global $menu
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin UX
 * @philosophy  #8 Inspire Confidence - Clean, organized admin interface
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Menu Item Count
 *
 * Checks if wp-admin has excessive top-level menu items (> 20)
 *
 * @verified Not yet tested
 */
class Test_Admin_Menu_Item_Count extends Diagnostic_Base
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

		global $menu;

		// Ensure $menu is available
		if (! isset($menu) || ! is_array($menu)) {
			return null;
		}

		// Count visible menu items (excluding separators)
		$menu_count = 0;
		$menu_items = array();
		$plugin_menus = 0;
		$core_menus = 0;

		foreach ($menu as $item) {
			// Skip empty items and separators
			if (empty($item) || empty($item[0]) || false !== strpos($item[4] ?? '', 'wp-menu-separator')) {
				continue;
			}

			$menu_count++;
			$menu_title = wp_strip_all_tags($item[0]);
			$menu_items[] = $menu_title;

			// Identify if it's a core menu or plugin menu
			$menu_slug = $item[2] ?? '';
			$is_core = in_array(
				$menu_slug,
				array('index.php', 'edit.php', 'upload.php', 'edit.php?post_type=page', 'edit-comments.php', 'themes.php', 'plugins.php', 'users.php', 'tools.php', 'options-general.php'),
				true
			);

			if ($is_core) {
				$core_menus++;
			} else {
				$plugin_menus++;
			}
		}

		// Threshold: More than 20 top-level menu items is excessive
		$threshold = 20;

		if ($menu_count <= $threshold) {
			return null; // Pass
		}

		return array(
			'id'           => 'admin-menu-item-count',
			'title'        => 'Too Many Admin Menu Items',
			'description'  => sprintf(
				'WordPress admin has %d top-level menu items (%d plugins/custom, %d core). This creates visual clutter and makes features hard to find. Recommended: Under %d items. Consider consolidating plugin menus or hiding unused items.',
				$menu_count,
				$plugin_menus,
				$core_menus,
				$threshold
			)
			'kb_link'      => 'https://wpshadow.com/kb/admin-menu-organization',
			'training_link' => 'https://wpshadow.com/training/organize-admin-menu',
			'auto_fixable' => false,
			'threat_level' => 35, // Medium priority - UX issue
			'module'       => 'admin-ux',
			'priority'     => 4,
			'meta'         => array(
				'menu_count'    => $menu_count,
				'plugin_menus'  => $plugin_menus,
				'core_menus'    => $core_menus,
				'threshold'     => $threshold,
				'sample_items'  => array_slice($menu_items, 0, 10), // First 10 items
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
			'name'        => 'Admin Menu Item Count',
			'category'    => 'admin-ux',
			'severity'    => 'medium',
			'description' => 'Detects excessive top-level menu items in WordPress admin',
		);
	}
}

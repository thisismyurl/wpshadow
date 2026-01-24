<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Dashboard Widgets
 *
 * Tests if wp-admin dashboard has too many widgets, which can cause:
 * - Slower dashboard load times
 * - Excessive API calls
 * - Information overload
 * - Poor user experience
 *
 * Pattern: Uses WordPress $wp_meta_boxes global to count dashboard widgets
 * Context: Requires admin context on dashboard page
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance & UX
 * @philosophy  #8 Inspire Confidence - Fast, focused dashboard experience
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Dashboard Widget Count
 *
 * Checks if dashboard has excessive widgets (> 8)
 *
 * @verified Not yet tested
 */
class Test_Admin_Dashboard_Widget_Count extends Diagnostic_Base
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

		global $wp_meta_boxes;

		// Get current screen
		$screen = get_current_screen();

		// Only check on dashboard
		if (! $screen || $screen->id !== 'dashboard') {
			// If not on dashboard, try to get dashboard widgets anyway
			if (! isset($wp_meta_boxes['dashboard'])) {
				return null;
			}
		}

		// Ensure dashboard widgets are loaded
		if (! isset($wp_meta_boxes['dashboard']) || ! is_array($wp_meta_boxes['dashboard'])) {
			return null;
		}

		// Count all dashboard widgets across all contexts (normal, side, column3, column4)
		$widget_count = 0;
		$widget_names = array();
		$contexts = array('normal', 'side', 'column3', 'column4');

		foreach ($contexts as $context) {
			if (! isset($wp_meta_boxes['dashboard'][$context])) {
				continue;
			}

			foreach ($wp_meta_boxes['dashboard'][$context] as $priority => $widgets) {
				if (! is_array($widgets)) {
					continue;
				}

				foreach ($widgets as $widget_id => $widget) {
					// Skip removed widgets
					if (empty($widget) || ! isset($widget['title'])) {
						continue;
					}

					$widget_count++;
					$widget_names[] = wp_strip_all_tags($widget['title']);
				}
			}
		}

		// Threshold: More than 8 dashboard widgets is excessive
		$threshold = 8;

		if ($widget_count <= $threshold) {
			return null; // Pass
		}

		return array(
			'id'           => 'admin-dashboard-widget-count',
			'title'        => 'Too Many Dashboard Widgets',
			'description'  => sprintf(
				'WordPress dashboard has %d active widgets. This slows dashboard loading and makes API calls on every page load. Recommended: Under %d widgets. Use Screen Options to hide unnecessary widgets.',
				$widget_count,
				$threshold
			)
			'kb_link'      => 'https://wpshadow.com/kb/optimize-dashboard-widgets',
			'training_link' => 'https://wpshadow.com/training/manage-dashboard-widgets',
			'auto_fixable' => false,
			'threat_level' => 40, // Medium priority
			'module'       => 'admin-performance',
			'priority'     => 5,
			'meta'         => array(
				'widget_count'  => $widget_count,
				'threshold'     => $threshold,
				'widget_names'  => array_slice($widget_names, 0, 10), // First 10 widgets
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
			'name'        => 'Admin Dashboard Widget Count',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects excessive dashboard widgets causing slow load times',
		);
	}
}

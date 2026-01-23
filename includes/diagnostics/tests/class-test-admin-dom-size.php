<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive DOM Size
 *
 * Tests if wp-admin page has excessive DOM elements, which causes:
 * - Slow browser rendering and repaints
 * - High memory consumption
 * - Sluggish JavaScript execution
 * - Poor user experience with lag/jank
 *
 * Pattern: Buffers admin output and counts HTML elements
 * Context: Requires admin context, buffers page output
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #7 Ridiculously Good - Fast, responsive admin interface
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin DOM Size
 *
 * Checks if admin page has excessive DOM elements (> 1500)
 *
 * @verified Not yet tested
 */
class Test_Admin_DOM_Size extends Diagnostic_Base
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

		// Buffer the current admin page output
		ob_start();
		do_action('admin_print_styles');
		do_action('admin_print_scripts');
		do_action('admin_notices');
		$buffer = ob_get_clean();

		// If we couldn't capture output, try alternative method
		if (empty($buffer)) {
			// Get a sample of the page HTML if possible
			global $wp_admin_bar;
			if (! $wp_admin_bar || ! method_exists($wp_admin_bar, 'get_nodes')) {
				return null; // Cannot test
			}
		}

		// Count HTML opening tags as proxy for DOM nodes
		// More accurate: Count all HTML elements including self-closing
		$element_count = $this->count_html_elements($buffer);

		// Also estimate based on WordPress admin complexity metrics
		global $wp_meta_boxes, $menu, $submenu;

		$widget_count = 0;
		if (isset($wp_meta_boxes['dashboard'])) {
			foreach ($wp_meta_boxes['dashboard'] as $context => $priority_boxes) {
				foreach ($priority_boxes as $priority => $boxes) {
					$widget_count += count($boxes);
				}
			}
		}

		$menu_count = is_array($menu) ? count($menu) : 0;
		$submenu_count = 0;
		if (is_array($submenu)) {
			foreach ($submenu as $parent => $items) {
				$submenu_count += count($items);
			}
		}

		// Estimate total DOM nodes
		// Average admin page: ~800-1200 nodes
		// Each widget adds ~50-100 nodes
		// Each menu item adds ~10-20 nodes
		$estimated_nodes = 800 + ($widget_count * 75) + ($menu_count * 15) + ($submenu_count * 12);

		// Use the higher of actual count or estimate
		$dom_nodes = max($element_count, $estimated_nodes);

		// Thresholds based on browser performance research
		$warning_threshold = 1500; // Google recommends < 1500
		$critical_threshold = 3000; // Severely degraded performance

		if ($dom_nodes <= $warning_threshold) {
			return null; // Pass
		}

		// Determine severity
		if ($dom_nodes >= $critical_threshold) {
			$threat_level = 65;
			$severity = 'critical';
		} else {
			$threat_level = 48;
			$severity = 'high';
		}

		return array(
			'id'           => 'admin-dom-size',
			'title'        => 'Excessive DOM Size in Admin',
			'description'  => sprintf(
				'WordPress admin page contains approximately %d DOM elements. Pages with more than %d elements suffer from slow rendering and poor responsiveness. Recommended: Reduce dashboard widgets and disable unused plugins.',
				$dom_nodes,
				$warning_threshold
			),
			'color'        => '#FF4500',
			'bg_color'     => '#FFF4F1',
			'kb_link'      => 'https://wpshadow.com/kb/reduce-admin-dom-size',
			'training_link' => 'https://wpshadow.com/training/optimize-admin-performance',
			'auto_fixable' => false,
			'threat_level' => $threat_level,
			'module'       => 'admin-performance',
			'priority'     => 13,
			'meta'         => array(
				'dom_nodes'           => $dom_nodes,
				'warning_threshold'   => $warning_threshold,
				'critical_threshold'  => $critical_threshold,
				'widget_count'        => $widget_count,
				'menu_count'          => $menu_count,
				'submenu_count'       => $submenu_count,
				'severity'            => $severity,
			),
		);
	}

	/**
	 * Count HTML elements in buffer
	 *
	 * @param string $html HTML content
	 * @return int Element count
	 */
	private function count_html_elements(string $html): int
	{
		if (empty($html)) {
			return 0;
		}

		// Count opening tags (includes self-closing)
		// Match: <tagname or <tagname> or <tagname />
		preg_match_all('/<[a-zA-Z][a-zA-Z0-9]*\b[^>]*>/i', $html, $matches);

		return count($matches[0] ?? array());
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin DOM Size',
			'category'    => 'admin-performance',
			'severity'    => 'high',
			'description' => 'Detects excessive DOM elements causing slow browser rendering',
		);
	}
}

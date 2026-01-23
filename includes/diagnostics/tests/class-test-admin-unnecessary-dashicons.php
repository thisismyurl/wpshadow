<?php

/**
 * WPShadow Admin Diagnostic Test: Unnecessary Dashicons
 *
 * Tests if Dashicons icon font loads when not needed, which causes:
 * - 60KB+ wasted download (icon font file)
 * - Render-blocking CSS request
 * - Poor performance on slow connections
 * - Unnecessary for pages without icon usage
 *
 * Pattern: Checks if Dashicons is enqueued on non-admin pages
 * Context: Can run in any context
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Performance
 * @philosophy  #7 Ridiculously Good - Load assets only where needed
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Unnecessary Dashicons
 *
 * Detects Dashicons loading on front-end or when unused
 *
 * @verified Not yet tested
 */
class Test_Admin_Unnecessary_Dashicons extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Check if Dashicons is enqueued
		$dashicons_enqueued = wp_style_is('dashicons', 'enqueued') || wp_style_is('dashicons', 'registered');

		if (! $dashicons_enqueued) {
			return null; // Not loaded, test doesn't apply
		}

		// Get current context
		$context = 'unknown';
		$should_load = true;

		if (is_admin()) {
			$context = 'admin';
			$should_load = true; // Expected in admin
		} elseif (is_user_logged_in()) {
			// Check if admin bar is showing
			if (is_admin_bar_showing()) {
				$context = 'front-end-with-admin-bar';
				$should_load = true; // Admin bar uses Dashicons
			} else {
				$context = 'front-end-logged-in';
				$should_load = false; // Probably unnecessary
			}
		} else {
			$context = 'front-end-logged-out';
			$should_load = false; // Definitely unnecessary
		}

		// If it should load in this context, no issue
		if ($should_load) {
			return null; // Pass
		}

		// Dashicons is loading when it shouldn't
		$impact = 'medium';
		$threat_level = 38;

		if ($context === 'front-end-logged-out') {
			$impact = 'high';
			$threat_level = 45; // Affects all visitors
		}

		// Get Dashicons file size (approximately 62KB)
		$dashicons_size = 62000;

		return array(
			'id'           => 'admin-unnecessary-dashicons',
			'title'        => 'Unnecessary Dashicons Icon Font Loading',
			'description'  => sprintf(
				'Dashicons icon font (~62KB) is loading in %s context where it\'s not needed. This wastes bandwidth and slows page load. Dashicons should only load in admin or when admin bar is visible.',
				$context
			),
			'color'        => '#FF6347',
			'bg_color'     => '#FFF5F3',
			'kb_link'      => 'https://wpshadow.com/kb/disable-dashicons',
			'training_link' => 'https://wpshadow.com/training/conditional-asset-loading',
			'auto_fixable' => true, // Can dequeue conditionally
			'threat_level' => $threat_level,
			'module'       => 'performance',
			'priority'     => 20,
			'meta'         => array(
				'context'        => $context,
				'file_size'      => $dashicons_size,
				'impact'         => $impact,
				'affects_visitors' => $context === 'front-end-logged-out',
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
			'name'        => 'Unnecessary Dashicons',
			'category'    => 'performance',
			'severity'    => 'medium',
			'description' => 'Detects Dashicons loading when not needed',
		);
	}
}

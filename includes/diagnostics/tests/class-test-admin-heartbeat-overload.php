<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Heartbeat/AJAX Activity
 *
 * Tests if WordPress Heartbeat API or AJAX activity is too frequent, which causes:
 * - Unnecessary server load and database queries
 * - Battery drain on laptops/mobile devices
 * - Wasted bandwidth with polling
 * - Poor server scalability
 *
 * Pattern: Checks Heartbeat settings and active AJAX listeners
 * Context: Requires admin context, checks wp.heartbeat settings
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance & Server Load
 * @philosophy  #7 Ridiculously Good - Efficient background activity
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Heartbeat Overload
 *
 * Checks for excessive Heartbeat frequency (< 30s interval)
 *
 * @verified Not yet tested
 */
class Test_Admin_Heartbeat_Overload extends Diagnostic_Base
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

		// Check Heartbeat settings
		$heartbeat_settings = $this->get_heartbeat_settings();

		if (! $heartbeat_settings) {
			return null; // Heartbeat disabled or cannot determine
		}

		$interval = $heartbeat_settings['interval'];
		$location = $heartbeat_settings['location'];

		// Recommended intervals:
		// - Dashboard/admin: 60 seconds
		// - Post editor: 15-30 seconds (for autosave)
		// - Front-end: Disabled or 120+ seconds

		$threshold_warning = 30; // Less than 30s is excessive
		$threshold_critical = 10; // Less than 10s is very excessive

		// Check if interval is too frequent
		if ($interval >= $threshold_warning) {
			return null; // Pass - Interval is reasonable
		}

		// Determine severity
		if ($interval < $threshold_critical) {
			$threat_level = 55;
			$severity = 'critical';
		} else {
			$threat_level = 42;
			$severity = 'medium';
		}

		// Calculate impact
		$requests_per_hour = 3600 / $interval;
		$requests_per_day = $requests_per_hour * 24;

		return array(
			'id'           => 'admin-heartbeat-overload',
			'title'        => 'Excessive WordPress Heartbeat Frequency',
			'description'  => sprintf(
				'WordPress Heartbeat is firing every %d seconds in %s. This generates %d requests per hour and %d requests per day per user, creating unnecessary server load. Recommended: Increase interval to 60 seconds for admin pages, 30 seconds for post editor.',
				$interval,
				$location,
				$requests_per_hour,
				$requests_per_day
			),
			'color'        => '#FF4500',
			'bg_color'     => '#FFF4F1',
			'kb_link'      => 'https://wpshadow.com/kb/optimize-heartbeat',
			'training_link' => 'https://wpshadow.com/training/reduce-server-load',
			'auto_fixable' => true, // Can adjust via filter
			'threat_level' => $threat_level,
			'module'       => 'admin-performance',
			'priority'     => 16,
			'meta'         => array(
				'interval'            => $interval,
				'location'            => $location,
				'requests_per_hour'   => $requests_per_hour,
				'requests_per_day'    => $requests_per_day,
				'recommended_interval' => $location === 'post-editor' ? 30 : 60,
				'severity'            => $severity,
			),
		);
	}

	/**
	 * Get current Heartbeat settings
	 *
	 * @return array|null Heartbeat settings or null if disabled
	 */
	private function get_heartbeat_settings(): ?array
	{
		// Check if Heartbeat is disabled
		if (! wp_script_is('heartbeat', 'enqueued') && ! wp_script_is('heartbeat', 'registered')) {
			return null; // Heartbeat disabled
		}

		// Get localized heartbeat settings
		global $wp_scripts;

		if (! isset($wp_scripts->registered['heartbeat'])) {
			return null;
		}

		$heartbeat_data = $wp_scripts->registered['heartbeat']->extra['data'] ?? '';

		// Default interval is 15 seconds in post editor, 60 seconds elsewhere
		$default_interval = 60;
		$location = 'dashboard';

		// Check current screen
		if (function_exists('get_current_screen')) {
			$screen = get_current_screen();
			if ($screen && $screen->base === 'post') {
				$default_interval = 15;
				$location = 'post-editor';
			}
		}

		// Try to parse interval from heartbeat settings
		// The interval can be filtered via 'heartbeat_settings'
		$settings = apply_filters('heartbeat_settings', array());
		$interval = $settings['interval'] ?? $default_interval;

		// Ensure interval is numeric
		$interval = absint($interval);
		if ($interval === 0) {
			$interval = $default_interval;
		}

		return array(
			'interval' => $interval,
			'location' => $location,
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
			'name'        => 'Admin Heartbeat Overload',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects excessive Heartbeat/AJAX polling frequency',
		);
	}
}

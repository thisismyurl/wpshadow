<?php

/**
 * WPShadow Admin Diagnostic Test: Outdated WordPress Version
 *
 * Tests if WordPress core is running an outdated version, which causes:
 * - Security vulnerabilities (old versions have known exploits)
 * - Missing performance improvements
 * - Compatibility issues with modern plugins
 * - Missing features and bug fixes
 *
 * Pattern: Compares current version to latest available version
 * Context: Can run in any context, checks WordPress version API
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Security & System Health
 * @philosophy  #7 Ridiculously Good - Stay current for security and performance
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Outdated WordPress Version
 *
 * Checks if WordPress is running an outdated version
 *
 * @verified Not yet tested
 */
class Test_Admin_Outdated_WordPress extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Get current WordPress version
		global $wp_version;
		$current_version = $wp_version;

		// Get latest version from WordPress.org
		$update_data = $this->get_wordpress_update_data();

		if (! $update_data) {
			// Cannot determine latest version, assume OK
			return null;
		}

		$latest_version = $update_data['latest_version'] ?? '';
		$is_major_update = $update_data['is_major'] ?? false;

		// If current version matches latest, we're good
		if (version_compare($current_version, $latest_version, '>=')) {
			return null; // Pass - Up to date
		}

		// Calculate version difference
		$version_parts = explode('.', $current_version);
		$latest_parts = explode('.', $latest_version);

		$major_behind = (int) ($latest_parts[0] ?? 0) - (int) ($version_parts[0] ?? 0);
		$minor_behind = (int) ($latest_parts[1] ?? 0) - (int) ($version_parts[1] ?? 0);

		// Determine threat level based on how far behind
		if ($major_behind > 0) {
			$threat_level = 70; // CRITICAL - Major version behind
			$severity = 'critical';
		} elseif ($minor_behind >= 3) {
			$threat_level = 60; // HIGH - 3+ minor versions behind
			$severity = 'high';
		} elseif ($minor_behind >= 1) {
			$threat_level = 45; // MEDIUM - 1-2 minor versions behind
			$severity = 'medium';
		} else {
			$threat_level = 30; // LOW - Patch version behind
			$severity = 'low';
		}

		return array(
			'id'           => 'admin-outdated-wordpress',
			'title'        => 'WordPress Core is Outdated',
			'description'  => sprintf(
				'WordPress %s is installed, but %s is available. Running outdated WordPress exposes your site to known security vulnerabilities. Update immediately to protect your site.',
				$current_version,
				$latest_version
			),
			'color'        => $this->get_color_by_severity($severity),
			'bg_color'     => $this->get_bg_color_by_severity($severity),
			'kb_link'      => 'https://wpshadow.com/kb/update-wordpress-core',
			'training_link' => 'https://wpshadow.com/training/safe-wordpress-updates',
			'auto_fixable' => false, // Core updates should be manual
			'threat_level' => $threat_level,
			'module'       => 'security',
			'priority'     => 1, // HIGHEST PRIORITY
			'meta'         => array(
				'current_version' => $current_version,
				'latest_version'  => $latest_version,
				'major_behind'    => $major_behind,
				'minor_behind'    => $minor_behind,
				'is_major_update' => $is_major_update,
				'severity'        => $severity,
			),
		);
	}

	/**
	 * Get WordPress update data from API
	 *
	 * @return array|null Update data or null if unavailable
	 */
	private function get_wordpress_update_data(): ?array
	{
		// First try to get from core update check
		if (! function_exists('get_core_updates')) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		$updates = get_core_updates();

		if (is_array($updates) && ! empty($updates)) {
			$latest_update = $updates[0];

			// Check if it's an upgrade (not already installed)
			if (isset($latest_update->response) && $latest_update->response === 'upgrade') {
				return array(
					'latest_version' => $latest_update->version ?? '',
					'is_major'       => strpos($latest_update->version ?? '', '.0') !== false,
				);
			}
		}

		// Fallback: Check transient directly
		$update_core = get_site_transient('update_core');

		if (is_object($update_core) && isset($update_core->updates) && is_array($update_core->updates)) {
			foreach ($update_core->updates as $update) {
				if (isset($update->response) && $update->response === 'upgrade') {
					return array(
						'latest_version' => $update->version ?? '',
						'is_major'       => strpos($update->version ?? '', '.0') !== false,
					);
				}
			}
		}

		// Cannot determine update status
		return null;
	}

	/**
	 * Get color based on severity
	 *
	 * @param string $severity Severity level
	 * @return string Hex color code
	 */
	private function get_color_by_severity(string $severity): string
	{
		$colors = array(
			'critical' => '#8B0000', // Dark red
			'high'     => '#DC143C', // Crimson
			'medium'   => '#FF6347', // Tomato
			'low'      => '#FF8C00', // Dark orange
		);

		return $colors[$severity] ?? '#FF6347';
	}

	/**
	 * Get background color based on severity
	 *
	 * @param string $severity Severity level
	 * @return string Hex color code
	 */
	private function get_bg_color_by_severity(string $severity): string
	{
		$colors = array(
			'critical' => '#FFEBEE', // Light red
			'high'     => '#FFF0F0', // Very light red
			'medium'   => '#FFF5F3', // Lightest red
			'low'      => '#FFF8F0', // Light orange
		);

		return $colors[$severity] ?? '#FFF5F3';
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Outdated WordPress Version',
			'category'    => 'security',
			'severity'    => 'varies', // Depends on how outdated
			'description' => 'Detects if WordPress core is running an outdated version',
		);
	}
}

<?php

/**
 * WPShadow Admin Diagnostic Test: Legacy jQuery Version
 *
 * Tests if WordPress is using outdated jQuery or jQuery UI versions, which causes:
 * - Slower JavaScript execution
 * - Security vulnerabilities (XSS, prototype pollution)
 * - Deprecated API usage requiring migrate script
 * - Compatibility issues with modern plugins
 *
 * Pattern: Checks jQuery version from $wp_scripts registry
 * Context: Can run in any context, checks registered scripts
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Security & Performance
 * @philosophy  #7 Ridiculously Good - Use modern, secure libraries
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Legacy jQuery Version
 *
 * Checks for outdated jQuery versions (< 3.6)
 *
 * @verified Not yet tested
 */
class Test_Admin_Legacy_jQuery extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		global $wp_scripts;

		if (! isset($wp_scripts->registered['jquery'])) {
			return null; // jQuery not registered
		}

		$jquery_script = $wp_scripts->registered['jquery'];
		$jquery_version = $jquery_script->ver ?? '';

		if (empty($jquery_version)) {
			// Try to detect from jQuery core
			if (isset($wp_scripts->registered['jquery-core'])) {
				$jquery_version = $wp_scripts->registered['jquery-core']->ver ?? '';
			}
		}

		if (empty($jquery_version)) {
			return null; // Cannot determine version
		}

		// Check jQuery Migrate (indicates old code compatibility mode)
		$has_migrate = wp_script_is('jquery-migrate', 'enqueued') ||
			wp_script_is('jquery-migrate', 'registered');

		// Current stable jQuery version is 3.7+ (as of 2024)
		// WordPress 5.6+ ships with jQuery 3.6+
		$recommended_version = '3.6.0';

		// Check if version is outdated
		if (version_compare($jquery_version, $recommended_version, '>=') && ! $has_migrate) {
			return null; // Pass - Modern jQuery without migrate
		}

		// Determine severity based on version age
		$major_version = (int) explode('.', $jquery_version)[0];

		if ($major_version < 3) {
			$threat_level = 60; // jQuery 1.x or 2.x - CRITICAL (security + performance)
			$severity = 'critical';
		} elseif (version_compare($jquery_version, '3.5.0', '<')) {
			$threat_level = 45; // jQuery 3.0-3.4 - MEDIUM
			$severity = 'medium';
		} else {
			$threat_level = 30; // jQuery 3.5+ but using migrate - LOW
			$severity = 'low';
		}

		// Build description based on findings
		$issues = array();

		if (version_compare($jquery_version, $recommended_version, '<')) {
			$issues[] = sprintf('jQuery %s is outdated (current: 3.7+)', $jquery_version);
		}

		if ($has_migrate) {
			$issues[] = 'jQuery Migrate is loaded (indicates deprecated code)';
		}

		if ($major_version < 3) {
			$issues[] = 'Known security vulnerabilities in jQuery 1.x/2.x';
		}

		return array(
			'id'           => 'admin-legacy-jquery',
			'title'        => 'Outdated jQuery Version Detected',
			'description'  => sprintf(
				'WordPress is using jQuery %s. Issues detected: %s. Recommended: Update to jQuery 3.6+ and remove jQuery Migrate dependency.',
				$jquery_version,
				implode(', ', $issues)
			),
			'color'        => $this->get_color_by_severity($severity),
			'bg_color'     => $this->get_bg_color_by_severity($severity),
			'kb_link'      => 'https://wpshadow.com/kb/update-jquery',
			'training_link' => 'https://wpshadow.com/training/modern-javascript',
			'auto_fixable' => false, // Requires theme/plugin updates
			'threat_level' => $threat_level,
			'module'       => 'security',
			'priority'     => 17,
			'meta'         => array(
				'jquery_version'       => $jquery_version,
				'recommended_version'  => '3.7.0',
				'has_migrate'          => $has_migrate,
				'major_version'        => $major_version,
				'security_concerns'    => $major_version < 3,
				'severity'             => $severity,
			),
		);
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
			'critical' => '#8B0000',
			'high'     => '#DC143C',
			'medium'   => '#FF6347',
			'low'      => '#FF8C00',
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
			'critical' => '#FFEBEE',
			'high'     => '#FFF0F0',
			'medium'   => '#FFF5F3',
			'low'      => '#FFF8F0',
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
			'name'        => 'Legacy jQuery Version',
			'category'    => 'security',
			'severity'    => 'varies',
			'description' => 'Detects outdated jQuery versions with security/performance issues',
		);
	}
}

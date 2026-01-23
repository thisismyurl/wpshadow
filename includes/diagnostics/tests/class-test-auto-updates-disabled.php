<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Auto-Updates Disabled
 *
 * Detects when automatic updates are disabled for WordPress core, plugins, or themes.
 * Disabled auto-updates leave sites vulnerable to security exploits.
 *
 * @since 1.2.0
 */
class Test_Auto_Updates_Disabled extends Diagnostic_Base
{

	/**
	 * Check for disabled auto-updates
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$disabled_items = [];

		// Check core updates
		$core_auto = get_option('auto_update_core', 'minor');
		if ($core_auto === false || $core_auto === 'off') {
			$disabled_items['core'] = true;
		}

		// Check plugin updates
		$plugin_auto = get_option('auto_update_plugins', true);
		if ($plugin_auto === false || $plugin_auto === 'off') {
			$disabled_items['plugins'] = true;
		}

		// Check theme updates
		$theme_auto = get_option('auto_update_themes', true);
		if ($theme_auto === false || $theme_auto === 'off') {
			$disabled_items['themes'] = true;
		}

		// If any auto-updates are disabled, it's an issue
		if (empty($disabled_items)) {
			return null;
		}

		// Calculate threat based on what's disabled
		$threat = 70;

		$issue_list = [];
		if (isset($disabled_items['core'])) {
			$issue_list[] = 'WordPress core';
		}
		if (isset($disabled_items['plugins'])) {
			$issue_list[] = 'plugins';
		}
		if (isset($disabled_items['themes'])) {
			$issue_list[] = 'themes';
		}

		return [
			'threat_level'    => $threat,
			'threat_color'    => 'red',
			'passed'          => false,
			'issue'           => sprintf(
				'Auto-updates disabled for: %s',
				implode(', ', $issue_list)
			),
			'metadata'        => [
				'core_auto_update'    => $core_auto,
				'plugin_auto_update'  => $plugin_auto,
				'theme_auto_update'   => $theme_auto,
				'disabled_count'      => count($disabled_items),
			],
			'kb_link'         => 'https://wpshadow.com/kb/auto-updates-security/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-security-updates/',
		];
	}

	/**
	 * Guardian Sub-Test: WordPress core auto-update status
	 *
	 * @return array Test result
	 */
	public static function test_core_auto_updates(): array
	{
		$core_auto = get_option('auto_update_core', 'minor');
		$enabled = $core_auto !== false && $core_auto !== 'off';

		return [
			'test_name'     => 'Core Auto-Updates Status',
			'status'        => $enabled ? 'Enabled' : 'Disabled',
			'setting_value' => $core_auto,
			'passed'        => $enabled,
			'description'   => $enabled ? 'WordPress core auto-updates enabled' : 'WordPress core auto-updates DISABLED (security risk)',
		];
	}

	/**
	 * Guardian Sub-Test: Plugin auto-update status
	 *
	 * @return array Test result
	 */
	public static function test_plugin_auto_updates(): array
	{
		$plugin_auto = get_option('auto_update_plugins', true);
		$enabled = $plugin_auto !== false && $plugin_auto !== 'off';

		return [
			'test_name'     => 'Plugin Auto-Updates Status',
			'status'        => $enabled ? 'Enabled' : 'Disabled',
			'setting_value' => is_bool($plugin_auto) ? ($plugin_auto ? 'true' : 'false') : $plugin_auto,
			'passed'        => $enabled,
			'description'   => $enabled ? 'Plugin auto-updates enabled' : 'Plugin auto-updates DISABLED (security risk)',
		];
	}

	/**
	 * Guardian Sub-Test: Theme auto-update status
	 *
	 * @return array Test result
	 */
	public static function test_theme_auto_updates(): array
	{
		$theme_auto = get_option('auto_update_themes', true);
		$enabled = $theme_auto !== false && $theme_auto !== 'off';

		return [
			'test_name'     => 'Theme Auto-Updates Status',
			'status'        => $enabled ? 'Enabled' : 'Disabled',
			'setting_value' => is_bool($theme_auto) ? ($theme_auto ? 'true' : 'false') : $theme_auto,
			'passed'        => $enabled,
			'description'   => $enabled ? 'Theme auto-updates enabled' : 'Theme auto-updates DISABLED (security risk)',
		];
	}

	/**
	 * Guardian Sub-Test: Overall auto-update safety assessment
	 *
	 * @return array Test result
	 */
	public static function test_auto_update_safety(): array
	{
		$core_auto = get_option('auto_update_core', 'minor');
		$plugin_auto = get_option('auto_update_plugins', true);
		$theme_auto = get_option('auto_update_themes', true);

		$core_enabled = $core_auto !== false && $core_auto !== 'off';
		$plugin_enabled = $plugin_auto !== false && $plugin_auto !== 'off';
		$theme_enabled = $theme_auto !== false && $theme_auto !== 'off';

		$enabled_count = (int) $core_enabled + (int) $plugin_enabled + (int) $theme_enabled;

		if ($enabled_count === 3) {
			$safety_level = 'high';
			$description = 'All auto-updates enabled - site is well protected';
		} elseif ($enabled_count === 2) {
			$safety_level = 'medium';
			$description = 'Partial auto-updates - consider enabling all';
		} else {
			$safety_level = 'low';
			$description = 'Most auto-updates disabled - significant security risk';
		}

		return [
			'test_name'       => 'Auto-Update Safety Assessment',
			'safety_level'    => $safety_level,
			'enabled_count'   => $enabled_count,
			'total_items'     => 3,
			'passed'          => $enabled_count === 3,
			'description'     => $description,
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Auto-Updates Disabled';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if automatic updates are enabled for security patches';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Security';
	}
}

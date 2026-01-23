<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Outdated Theme
 *
 * Detects when WordPress theme is outdated or no longer maintained.
 * Outdated themes may have security vulnerabilities and compatibility issues.
 *
 * @since 1.2.0
 */
class Test_Outdated_Theme extends Diagnostic_Base
{

	/**
	 * Check for outdated theme
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$theme = wp_get_theme();
		$theme_data = self::get_theme_update_info($theme);

		if (! $theme_data || ! isset($theme_data['has_update']) || ! $theme_data['has_update']) {
			return null; // Theme is up to date
		}

		$threat = isset($theme_data['days_since_update']) && $theme_data['days_since_update'] > 365 ? 60 : 40;

		return [
			'threat_level'    => $threat,
			'threat_color'    => 'yellow',
			'passed'          => false,
			'issue'           => sprintf(
				'Theme "%s" is outdated',
				$theme->get('Name')
			),
			'metadata'        => [
				'theme_name'      => $theme->get('Name'),
				'current_version' => $theme->get('Version'),
				'latest_version'  => $theme_data['latest_version'] ?? 'Unknown',
				'last_updated'    => $theme_data['last_updated'] ?? 'Unknown',
			],
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-theme-updates/',
			'training_link'   => 'https://wpshadow.com/training/theme-maintenance/',
		];
	}

	/**
	 * Guardian Sub-Test: Theme version
	 *
	 * @return array Test result
	 */
	public static function test_theme_version(): array
	{
		$theme = wp_get_theme();

		return [
			'test_name'     => 'Active Theme Version',
			'theme_name'    => $theme->get('Name'),
			'version'       => $theme->get('Version'),
			'author'        => $theme->get('Author'),
			'description'   => sprintf('Theme: %s v%s', $theme->get('Name'), $theme->get('Version')),
		];
	}

	/**
	 * Guardian Sub-Test: Theme update availability
	 *
	 * @return array Test result
	 */
	public static function test_theme_updates(): array
	{
		$theme = wp_get_theme();
		$theme_data = self::get_theme_update_info($theme);
		$has_update = $theme_data ? $theme_data['has_update'] : false;

		return [
			'test_name'       => 'Theme Updates Available',
			'has_update'      => $has_update,
			'current_version' => $theme->get('Version'),
			'latest_version'  => $theme_data['latest_version'] ?? 'Unknown',
			'passed'          => ! $has_update,
			'description'     => $has_update ? sprintf('Update available: %s', $theme_data['latest_version'] ?? 'Unknown') : 'Theme is up to date',
		];
	}

	/**
	 * Guardian Sub-Test: Theme maintenance status
	 *
	 * @return array Test result
	 */
	public static function test_theme_maintenance(): array
	{
		$theme = wp_get_theme();
		$theme_data = self::get_theme_update_info($theme);
		$days_since = $theme_data['days_since_update'] ?? 0;

		$status = $days_since > 365 ? 'Possibly abandoned' : ($days_since > 180 ? 'Inactive' : 'Active');
		$risk = $status === 'Possibly abandoned' ? 'high' : ($status === 'Inactive' ? 'medium' : 'low');

		return [
			'test_name'         => 'Theme Maintenance Status',
			'last_updated_days' => $days_since,
			'status'            => $status,
			'risk_level'        => $risk,
			'passed'            => $risk === 'low',
			'description'       => sprintf('Last updated %d days ago - %s', $days_since, $status),
		];
	}

	/**
	 * Guardian Sub-Test: Theme security issues
	 *
	 * @return array Test result
	 */
	public static function test_theme_security(): array
	{
		$theme = wp_get_theme();
		$security_issues = self::check_theme_security($theme);

		return [
			'test_name'        => 'Theme Security Check',
			'issues_found'     => count($security_issues),
			'security_issues'  => $security_issues,
			'passed'           => empty($security_issues),
			'description'      => empty($security_issues) ? 'No known security issues' : sprintf('%d potential issues detected', count($security_issues)),
		];
	}

	/**
	 * Get theme update information
	 *
	 * @param WP_Theme $theme Theme object
	 * @return array|null Update info or null
	 */
	private static function get_theme_update_info($theme): ?array
	{
		$theme_slug = $theme->get_stylesheet();
		$transient_key = 'site_transient_update_themes';
		$update_themes = get_transient($transient_key);

		if (! $update_themes || ! isset($update_themes->response[$theme_slug])) {
			return null;
		}

		$update_data = $update_themes->response[$theme_slug];

		return [
			'has_update'      => true,
			'latest_version'  => $update_data['new_version'] ?? 'Unknown',
			'last_updated'    => $update_data['last_updated'] ?? 'Unknown',
			'days_since_update' => self::calculate_days_since($update_data['last_updated'] ?? null),
		];
	}

	/**
	 * Calculate days since last update
	 *
	 * @param string|null $date Update date
	 * @return int Days
	 */
	private static function calculate_days_since(?string $date): int
	{
		if (! $date) {
			return 0;
		}

		try {
			$update_time = strtotime($date);
			$days = (time() - $update_time) / 86400;
			return (int) ceil($days);
		} catch (\Exception $e) {
			return 0;
		}
	}

	/**
	 * Check theme for security issues
	 *
	 * @param WP_Theme $theme Theme object
	 * @return array Issues
	 */
	private static function check_theme_security($theme): array
	{
		$issues = [];
		$theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();

		// Check for common vulnerable plugins patterns
		$files_to_check = [
			$theme_dir . '/functions.php',
			$theme_dir . '/template.php',
		];

		foreach ($files_to_check as $file) {
			if (file_exists($file)) {
				$content = file_get_contents($file);

				// Look for eval
				if (strpos($content, 'eval(') !== false) {
					$issues[] = 'Theme uses eval() function (security risk)';
				}

				// Look for base64 encoded content
				if (preg_match('/base64_decode\s*\(\s*["\']/', $content)) {
					$issues[] = 'Theme contains suspicious base64 encoding';
				}
			}
		}

		return $issues;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Outdated Theme';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if WordPress theme is current and actively maintained';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Updates';
	}
}

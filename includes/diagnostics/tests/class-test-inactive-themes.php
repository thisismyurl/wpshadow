<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Inactive Themes
 *
 * Detects installed but inactive themes which waste disk space and may pose security risks.
 * Every active site needs only 1-2 themes maximum.
 *
 * @since 1.2.0
 */
class Test_Inactive_Themes extends Diagnostic_Base
{

	/**
	 * Check for inactive themes
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$all_themes = wp_get_themes();
		$active_theme = wp_get_theme();
		$active_slug = $active_theme->get_stylesheet();

		// Count inactive themes
		$inactive_count = count($all_themes) - 1; // -1 for active theme

		// Threshold: 3+ inactive themes is wasteful
		if ($inactive_count < 3) {
			return null;
		}

		// Calculate total size of inactive themes
		$total_size = 0;
		$inactive_themes = [];

		foreach ($all_themes as $slug => $theme) {
			if ($slug !== $active_slug) {
				$theme_path = $theme->get_theme_root() . '/' . $slug;
				$size = self::get_dir_size($theme_path);
				$total_size += $size;

				$inactive_themes[] = [
					'name'    => $theme->get('Name'),
					'slug'    => $slug,
					'version' => $theme->get('Version'),
					'author'  => $theme->get('Author'),
					'size'    => $size,
				];
			}
		}

		// Sort by size descending
		usort($inactive_themes, function ($a, $b) {
			return $b['size'] <=> $a['size'];
		});

		// Calculate threat level
		$threat = min(40, $inactive_count * 3);

		return [
			'threat_level'    => $threat,
			'threat_color'    => $threat > 30 ? 'red' : 'yellow',
			'passed'          => false,
			'issue'           => sprintf(
				'Found %d inactive themes using %s disk space',
				$inactive_count,
				self::format_bytes($total_size)
			),
			'metadata'        => [
				'inactive_count' => $inactive_count,
				'total_size'     => $total_size,
				'formatted_size' => self::format_bytes($total_size),
				'top_themes'     => array_slice($inactive_themes, 0, 5),
			],
			'kb_link'         => 'https://wpshadow.com/kb/inactive-themes/',
			'training_link'   => 'https://wpshadow.com/training/cleanup-themes/',
		];
	}

	/**
	 * Guardian Sub-Test: Count inactive vs active themes
	 *
	 * @return array Test result
	 */
	public static function test_inactive_themes_count(): array
	{
		$all_themes = wp_get_themes();
		$inactive_count = count($all_themes) - 1;

		return [
			'test_name'   => 'Inactive Themes Count',
			'total'       => count($all_themes),
			'inactive'    => $inactive_count,
			'passed'      => $inactive_count < 3,
			'description' => sprintf('Detected %d total themes, %d inactive', count($all_themes), $inactive_count),
		];
	}

	/**
	 * Guardian Sub-Test: Disk space used by inactive themes
	 *
	 * @return array Test result
	 */
	public static function test_inactive_themes_size(): array
	{
		$all_themes = wp_get_themes();
		$active_theme = wp_get_theme();
		$active_slug = $active_theme->get_stylesheet();
		$total_size = 0;

		foreach ($all_themes as $slug => $theme) {
			if ($slug !== $active_slug) {
				$theme_path = $theme->get_theme_root() . '/' . $slug;
				$total_size += self::get_dir_size($theme_path);
			}
		}

		return [
			'test_name'      => 'Inactive Themes Disk Space',
			'total_bytes'    => $total_size,
			'formatted_size' => self::format_bytes($total_size),
			'passed'         => $total_size < 50 * 1024 * 1024, // 50MB threshold
			'description'    => sprintf('Inactive themes use %s disk space', self::format_bytes($total_size)),
		];
	}

	/**
	 * Guardian Sub-Test: Detailed list of inactive themes
	 *
	 * @return array Test result
	 */
	public static function test_inactive_themes_list(): array
	{
		$all_themes = wp_get_themes();
		$active_theme = wp_get_theme();
		$active_slug = $active_theme->get_stylesheet();
		$inactive_list = [];

		foreach ($all_themes as $slug => $theme) {
			if ($slug !== $active_slug) {
				$inactive_list[] = [
					'name'    => $theme->get('Name'),
					'slug'    => $slug,
					'version' => $theme->get('Version'),
					'author'  => $theme->get('Author'),
				];
			}
		}

		return [
			'test_name'    => 'Inactive Themes List',
			'inactive_themes' => $inactive_list,
			'count'        => count($inactive_list),
			'passed'       => count($inactive_list) < 3,
			'description'  => sprintf('Found %d inactive themes', count($inactive_list)),
		];
	}

	/**
	 * Get total size of a directory recursively
	 *
	 * @param string $path Directory path
	 * @return int Total size in bytes
	 */
	private static function get_dir_size(string $path): int
	{
		$size = 0;

		if (! is_dir($path)) {
			return 0;
		}

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ($iterator as $file) {
				if (is_file($file)) {
					$size += filesize($file);
				}
			}
		} catch (\Exception $e) {
			// Silently fail if directory not accessible
			return 0;
		}

		return $size;
	}

	/**
	 * Format bytes as human-readable size
	 *
	 * @param int $bytes Number of bytes
	 * @return string Formatted size
	 */
	private static function format_bytes(int $bytes): string
	{
		$units = ['B', 'KB', 'MB', 'GB'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow));

		return round($bytes, 2) . ' ' . $units[$pow];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Inactive Themes';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Detects unused theme installations that waste disk space';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Performance';
	}
}

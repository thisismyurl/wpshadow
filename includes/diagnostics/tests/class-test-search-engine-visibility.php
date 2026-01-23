<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Search Engine Visibility
 *
 * Checks if site is discoverable by search engines.
 * Accidentally blocked search engines prevents organic traffic.
 *
 * @since 1.2.0
 */
class Test_Search_Engine_Visibility extends Diagnostic_Base
{

	/**
	 * Check search engine visibility
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$visibility = self::check_search_visibility();

		if ($visibility['threat_level'] === 0) {
			return null;
		}

		return [
			'threat_level'    => $visibility['threat_level'],
			'threat_color'    => 'red',
			'passed'          => false,
			'issue'           => $visibility['issue'],
			'metadata'        => $visibility,
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-search-engine-visibility/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-seo-basics/',
		];
	}

	/**
	 * Guardian Sub-Test: Robots.txt existence
	 *
	 * @return array Test result
	 */
	public static function test_robots_txt(): array
	{
		$robots_path = ABSPATH . 'robots.txt';
		$has_robots = file_exists($robots_path);

		$content = '';
		if ($has_robots) {
			$content = file_get_contents($robots_path);
		}

		return [
			'test_name'   => 'Robots.txt File',
			'exists'      => $has_robots,
			'passed'      => $has_robots,
			'description' => $has_robots ? 'robots.txt is present' : 'robots.txt not found',
		];
	}

	/**
	 * Guardian Sub-Test: Sitemap presence
	 *
	 * @return array Test result
	 */
	public static function test_sitemap(): array
	{
		$sitemap_url = home_url('/sitemap.xml');

		$response = wp_remote_get($sitemap_url, ['timeout' => 5]);

		$has_sitemap = ! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;

		return [
			'test_name'   => 'XML Sitemap',
			'has_sitemap' => $has_sitemap,
			'passed'      => $has_sitemap,
			'description' => $has_sitemap ? 'XML sitemap is accessible' : 'XML sitemap not found',
		];
	}

	/**
	 * Guardian Sub-Test: Discourage search indexing
	 *
	 * @return array Test result
	 */
	public static function test_search_indexing(): array
	{
		$blog_public = get_option('blog_public');

		// 1 = allow search engines, 0 = discourage
		$is_visible = intval($blog_public) === 1;

		return [
			'test_name'  => 'Search Indexing',
			'visible'    => $is_visible,
			'passed'     => $is_visible,
			'description' => $is_visible ? 'Site is visible to search engines' : 'Site is HIDDEN from search engines',
		];
	}

	/**
	 * Guardian Sub-Test: SEO plugin detection
	 *
	 * @return array Test result
	 */
	public static function test_seo_plugin(): array
	{
		$active_plugins = get_plugins();

		$seo_plugins = [
			'wordpress-seo/wp-seo.php' => 'Yoast SEO',
			'seo-by-rank-math/rank-math.php' => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'autodescription/autodescription.php' => 'The SEO Framework',
		];

		$active_seo = null;
		foreach ($seo_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$active_seo = $plugin_name;
				break;
			}
		}

		return [
			'test_name'  => 'SEO Plugin',
			'active_seo' => $active_seo,
			'passed'     => $active_seo !== null,
			'description' => $active_seo ?? 'No SEO plugin installed',
		];
	}

	/**
	 * Check search engine visibility
	 *
	 * @return array Visibility check
	 */
	private static function check_search_visibility(): array
	{
		$blog_public = get_option('blog_public');
		$is_visible = intval($blog_public) === 1;

		$threat_level = 0;
		$threat_color = 'green';
		$issues = [];

		if (! $is_visible) {
			$issues[] = 'Site is hidden from search engines';
			$threat_level = 95;
			$threat_color = 'red';
			$issue = 'CRITICAL: Search engines are discouraged from indexing this site';

			return [
				'threat_level'   => $threat_level,
				'threat_color'   => $threat_color,
				'issue'          => $issue,
				'is_visible'     => false,
			];
		}

		// Check for robots.txt
		$robots_path = ABSPATH . 'robots.txt';
		if (! file_exists($robots_path)) {
			$issues[] = 'No robots.txt file';
			$threat_level = 20;
		}

		// Check for sitemap
		$sitemap_url = home_url('/sitemap.xml');
		$response = wp_remote_get($sitemap_url, ['timeout' => 5]);
		$has_sitemap = ! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;

		if (! $has_sitemap) {
			$issues[] = 'No XML sitemap found';
			$threat_level = max($threat_level, 15);
		}

		// Check for SEO plugin
		$active_plugins = get_plugins();
		$seo_plugins = [
			'wordpress-seo/wp-seo.php',
			'seo-by-rank-math/rank-math.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'autodescription/autodescription.php',
		];

		$has_seo = false;
		foreach ($seo_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_seo = true;
				break;
			}
		}

		if (! $has_seo) {
			$issues[] = 'No SEO plugin installed';
			$threat_level = max($threat_level, 10);
		}

		if (empty($issues)) {
			$issue = 'Search engine visibility is properly configured';
		} else {
			$issue = implode('; ', $issues);
		}

		return [
			'threat_level' => $threat_level,
			'threat_color' => $threat_color,
			'issue'        => $issue,
			'is_visible'   => $is_visible,
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Search Engine Visibility';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if site is discoverable by search engines and properly indexed';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'SEO';
	}
}

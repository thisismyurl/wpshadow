<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: CDN Configuration
 *
 * Checks if Content Delivery Network (CDN) is configured for static assets.
 * CDN integration can reduce bandwidth costs and improve global load times.
 *
 * @since 1.2.0
 */
class Test_Cdn_Configuration extends Diagnostic_Base
{

	/**
	 * Check CDN configuration
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$cdn_config = self::analyze_cdn_setup();

		if ($cdn_config['threat_level'] === 0) {
			return null;
		}

		return [
			'threat_level'    => $cdn_config['threat_level'],
			'threat_color'    => 'yellow',
			'passed'          => false,
			'issue'           => $cdn_config['issue'],
			'metadata'        => $cdn_config,
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-cdn-setup/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-cdn-performance/',
		];
	}

	/**
	 * Guardian Sub-Test: CDN plugin detection
	 *
	 * @return array Test result
	 */
	public static function test_cdn_plugin(): array
	{
		$active_plugins = get_plugins();

		$cdn_plugins = [
			'cloudflare/cloudflare.php' => 'Cloudflare',
			'wp-super-cache/wp-cache.php' => 'WP Super Cache CDN',
			'stackpath/stackpath.php' => 'StackPath',
			'bunnycdn/bunnycdn.php' => 'BunnyCDN',
		];

		$active_cdn = null;
		foreach ($cdn_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$active_cdn = $plugin_name;
				break;
			}
		}

		return [
			'test_name'  => 'CDN Plugin',
			'active_cdn' => $active_cdn,
			'passed'     => $active_cdn !== null,
			'description' => $active_cdn ?? 'No CDN plugin detected',
		];
	}

	/**
	 * Guardian Sub-Test: Static asset caching headers
	 *
	 * @return array Test result
	 */
	public static function test_static_asset_headers(): array
	{
		$has_cache_headers = false;

		// Check if cache control headers are being set
		$headers = wp_get_http_headers(home_url('/wp-content/themes/twentytwentythree/style.css'));

		if ($headers && isset($headers['cache-control'])) {
			$has_cache_headers = true;
		}

		return [
			'test_name'           => 'Static Asset Cache Headers',
			'has_cache_headers'   => $has_cache_headers,
			'passed'              => $has_cache_headers,
			'description'         => $has_cache_headers ? 'Cache headers configured for static assets' : 'Cache headers not configured',
		];
	}

	/**
	 * Guardian Sub-Test: Asset minification
	 *
	 * @return array Test result
	 */
	public static function test_asset_minification(): array
	{
		$active_plugins = get_plugins();

		$minification_plugins = [
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php' => 'WP Super Cache',
			'autoptimize/autoptimize.php' => 'Autoptimize',
		];

		$has_minification = false;
		foreach ($minification_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$has_minification = true;
				break;
			}
		}

		return [
			'test_name'        => 'Asset Minification',
			'has_minification' => $has_minification,
			'passed'           => $has_minification,
			'description'      => $has_minification ? 'Asset minification plugin active' : 'No asset minification plugin detected',
		];
	}

	/**
	 * Guardian Sub-Test: DNS prefetching
	 *
	 * @return array Test result
	 */
	public static function test_dns_prefetching(): array
	{
		// Check if theme or plugin is setting DNS prefetch headers
		$has_dns_prefetch = has_action('wp_head', 'wp_resource_hints');

		return [
			'test_name'       => 'DNS Prefetching',
			'dns_prefetch'    => $has_dns_prefetch,
			'passed'          => $has_dns_prefetch,
			'description'     => $has_dns_prefetch ? 'DNS prefetching is configured' : 'DNS prefetching not configured',
		];
	}

	/**
	 * Analyze CDN setup
	 *
	 * @return array CDN analysis
	 */
	private static function analyze_cdn_setup(): array
	{
		$active_plugins = get_plugins();

		$threat_level = 0;
		$issues = [];

		// Check for CDN plugin
		$cdn_plugins = [
			'cloudflare/cloudflare.php',
			'stackpath/stackpath.php',
			'bunnycdn/bunnycdn.php',
		];

		$has_cdn = false;
		foreach ($cdn_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_cdn = true;
				break;
			}
		}

		if (! $has_cdn) {
			$issues[] = 'No CDN configured';
			$threat_level = 15;
		}

		// Check for asset optimization
		$optimization_plugins = [
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'autoptimize/autoptimize.php',
		];

		$has_optimization = false;
		foreach ($optimization_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_optimization = true;
				break;
			}
		}

		if (! $has_optimization) {
			$issues[] = 'No asset optimization plugin';
			$threat_level = max($threat_level, 20);
		}

		$issue = ! empty($issues) ? implode('; ', $issues) : 'CDN is properly configured';

		return [
			'threat_level'  => $threat_level,
			'issue'         => $issue,
			'has_cdn'       => $has_cdn,
			'has_optimization' => $has_optimization,
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'CDN Configuration';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if Content Delivery Network is configured for optimal static asset delivery';
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

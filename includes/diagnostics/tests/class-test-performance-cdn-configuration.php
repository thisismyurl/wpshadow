<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: CDN Configuration (Performance)
 *
 * Checks if CDN is configured for static assets
 * Philosophy: Show value (#9) - CDN reduces latency globally
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Performance_CdnConfiguration extends Diagnostic_Base
{

	public static function check(): ?array
	{
		// Check if CDN is configured
		$plugins = get_plugins();
		$cdn_active = false;

		foreach ($plugins as $plugin_file => $plugin_data) {
			if (
				stripos($plugin_file, 'cdn') !== false ||
				stripos($plugin_file, 'cloudflare') !== false ||
				stripos($plugin_file, 'bunnycdn') !== false ||
				stripos($plugin_file, 'stackpath') !== false
			) {
				if (is_plugin_active($plugin_file)) {
					$cdn_active = true;
					break;
				}
			}
		}

		// Check if using WordPress.com or similar CDN service
		$siteurl = get_option('siteurl');
		if (strpos($siteurl, 'wordpress.com') === false && !$cdn_active) {
			return [
				'id' => 'cdn-configuration',
				'title' => __('CDN not configured', 'wpshadow'),
				'description' => __('Configure a CDN (Cloudflare, BunnyCDN, etc.) to serve static assets faster to users worldwide.', 'wpshadow'),
				'severity' => 'low',
				'threat_level' => 25,
			];
		}

		return null;
	}

	public static function test_live_cdn_configuration(): array
	{
		$result = self::check();

		if (null === $result) {
			return [
				'passed' => true,
				'message' => __('CDN is configured', 'wpshadow'),
			];
		}

		return [
			'passed' => false,
			'message' => $result['description'],
		];
	}
}

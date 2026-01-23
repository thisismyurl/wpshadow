<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Caching_Headers extends Diagnostic_Base
{

	protected static $slug = 'test-performance-caching-headers';
	protected static $title = 'Browser Caching Test';
	protected static $description = 'Tests for proper cache headers (Cache-Control, Expires)';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');

		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		$has_cache_control = isset($headers['cache-control']);
		$has_expires = isset($headers['expires']);
		$has_etag = isset($headers['etag']);

		if (!$has_cache_control && !$has_expires) {
			return [
				'id' => 'performance-no-cache-headers',
				'title' => 'Missing Cache Headers',
				'description' => 'No Cache-Control or Expires headers found. Browser caching can dramatically reduce repeat-visit load times.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/browser-caching/',
				'training_link' => 'https://wpshadow.com/training/caching/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'module' => 'Performance',
				'priority' => 2,
				'meta' => ['has_cache_control' => false, 'has_expires' => false, 'has_etag' => $has_etag],
			];
		}

		// Check if caching is too short
		if ($has_cache_control) {
			$cache_control = $headers['cache-control'];
			if (preg_match('/max-age=([0-9]+)/i', $cache_control, $match)) {
				$max_age = (int)$match[1];

				// Less than 1 hour is too short for static assets
				if ($max_age < 3600) {
					return [
						'id' => 'performance-cache-too-short',
						'title' => 'Cache Duration Too Short',
						'description' => sprintf('Cache max-age is only %d seconds. For static assets, use at least 1 week (604800 seconds).', $max_age),
						'color' => '#2196f3',
						'bg_color' => '#e3f2fd',
						'kb_link' => 'https://wpshadow.com/kb/cache-duration/',
						'training_link' => 'https://wpshadow.com/training/caching/',
						'auto_fixable' => false,
						'threat_level' => 30,
						'module' => 'Performance',
						'priority' => 3,
						'meta' => ['max_age' => $max_age],
					];
				}
			}
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Browser Caching', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for proper cache headers (Cache-Control, Expires).', 'wpshadow');
	}
}

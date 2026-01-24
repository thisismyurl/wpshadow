<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Cache_Duration_Short extends Diagnostic_Base
{

	protected static $slug = 'test-performance-cache-duration-short';
	protected static $title = 'Cache Duration Short Test';
	protected static $description = 'Tests for cache max-age shorter than 1 hour.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');
		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		if (!isset($headers['cache-control'])) {
			return null; // Missing cache headers handled separately
		}

		$cache_control = $headers['cache-control'];

		if (preg_match('/max-age=([0-9]+)/i', $cache_control, $match)) {
			$max_age = (int) $match[1];

			if ($max_age < 3600) {
				return [
					'id' => 'performance-cache-too-short',
					'title' => 'Cache Duration Too Short',
					'description' => sprintf('Cache max-age is only %d seconds. For static assets, use at least 1 week (604800 seconds).', $max_age)
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

		return null;
	}

	public static function get_name(): string
	{
		return __('Cache Duration Too Short', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks that cache max-age is at least 1 hour.', 'wpshadow');
	}
}

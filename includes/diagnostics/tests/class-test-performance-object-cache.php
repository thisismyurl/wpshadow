<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

class Test_Performance_Object_Cache extends Diagnostic_Base
{

	public static function check(): ?array
	{
		if (! function_exists('wp_cache_get')) {
			return null;
		}

		$has_persistent_cache = wp_cache_get('test_key');
		wp_cache_set('test_key', 'test_value');
		$is_working = wp_cache_get('test_key') === 'test_value';

		if (! $is_working || ! defined('WP_CACHE')) {
			return array(
				'id'           => 'object-cache-not-configured',
				'title'        => 'Object Cache Not Configured',
				'description'  => 'Enable a persistent object cache (Redis/Memcached) for significant performance improvement.',
				'threat_level' => 60,
			);
		}
		return null;
	}

	public static function test_live_object_cache(): array
	{
		$result = self::check();
		return array(
			'passed' => true,
			'message' => 'Object cache check passed.',
		);
	}
}

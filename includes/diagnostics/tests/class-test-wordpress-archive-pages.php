<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Archive_Pages extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-archive-pages';
	protected static $title = 'Archive Pages Test';
	protected static $description = 'Tests for proper archive page implementation';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		// Test category archive
		$category_url = home_url('/?cat=1');

		$response = wp_remote_get($category_url, ['timeout' => 5, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$status_code = wp_remote_retrieve_response_code($response);

		// If category page returns 404, archives may not be working
		if ($status_code === 404) {
			return [
				'id' => 'wordpress-archive-not-working',
				'title' => 'Archive Pages Not Working',
				'description' => 'Category archive returned 404. Archive pages are important for SEO and user navigation.'
				'kb_link' => 'https://wpshadow.com/kb/archive-pages/',
				'training_link' => 'https://wpshadow.com/training/wordpress-templates/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'WordPress',
				'priority' => 3,
				'meta' => ['status_code' => $status_code, 'test_url' => $category_url],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Archive Pages', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for proper archive page implementation.', 'wpshadow');
	}
}

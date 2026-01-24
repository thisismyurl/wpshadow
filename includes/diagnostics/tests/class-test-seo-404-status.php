<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_404_Status extends Diagnostic_Base
{

	protected static $slug = 'test-seo-404-status';
	protected static $title = '404 Status Code Test';
	protected static $description = 'Tests that missing pages return proper 404 status.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$test_url = home_url('/this-page-does-not-exist-' . wp_rand(1000, 9999) . '/');
		$response = wp_remote_get($test_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$status_code = wp_remote_retrieve_response_code($response);

		if ($status_code !== 404) {
			return [
				'id' => 'seo-404-wrong-status',
				'title' => '404 Pages Not Returning 404 Status',
				'description' => sprintf('Non-existent page returned status %d instead of 404. This can confuse search engines and waste crawl budget.', $status_code)
				'kb_link' => 'https://wpshadow.com/kb/404-status-codes/',
				'training_link' => 'https://wpshadow.com/training/technical-seo/',
				'auto_fixable' => false,
				'threat_level' => 55,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => ['status_code' => $status_code, 'expected' => 404],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('404 Status Code', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks that missing pages return an HTTP 404.', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_404_Page extends Diagnostic_Base
{

	protected static $slug = 'test-seo-404-page';
	protected static $title = '404 Page Test';
	protected static $description = 'Tests for custom 404 page with helpful content';

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
				'description' => sprintf('Non-existent page returned status %d instead of 404. This can confuse search engines and waste crawl budget.', $status_code),
				'color' => '#ff5722',
				'bg_color' => '#ffebee',
				'kb_link' => 'https://wpshadow.com/kb/404-status-codes/',
				'training_link' => 'https://wpshadow.com/training/technical-seo/',
				'auto_fixable' => false,
				'threat_level' => 55,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => ['status_code' => $status_code, 'expected' => 404],
			];
		}

		$html = wp_remote_retrieve_body($response);

		// Check for helpful 404 content
		$has_search = preg_match('/<form[^>]*class=["\'][^"\']*search|<input[^>]*type=["\']search/i', $html);
		$has_links = preg_match_all('/<a[^>]*href=/i', $html, $links);
		$link_count = count($links[0]);

		if (!$has_search && $link_count < 3) {
			return [
				'id' => 'seo-404-page-not-helpful',
				'title' => '404 Page Not Helpful',
				'description' => '404 page exists but lacks helpful elements (search box, navigation links). Help users find what they\'re looking for.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/404-page-optimization/',
				'training_link' => 'https://wpshadow.com/training/ux-optimization/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'UX',
				'priority' => 3,
				'meta' => ['has_search' => false, 'link_count' => $link_count],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('404 Page Configuration', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for custom 404 page with helpful content.', 'wpshadow');
	}
}

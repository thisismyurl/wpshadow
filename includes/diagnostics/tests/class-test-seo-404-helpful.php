<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_404_Helpful extends Diagnostic_Base
{

	protected static $slug = 'test-seo-404-helpful';
	protected static $title = 'Helpful 404 Page Test';
	protected static $description = 'Tests that 404 page offers helpful navigation.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$test_url = home_url('/this-page-does-not-exist-' . wp_rand(1000, 9999) . '/');
		$response = wp_remote_get($test_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$status_code = wp_remote_retrieve_response_code($response);

		// Only evaluate helpfulness if the status is correct.
		if ($status_code !== 404) {
			return null;
		}

		$html_body = wp_remote_retrieve_body($response);
		$has_search = preg_match('/<form[^>]*class=["\'][^"\']*search|<input[^>]*type=["\']search/i', $html_body);
		$has_links = preg_match_all('/<a[^>]*href=/i', $html_body, $links);
		$link_count = is_array($links) && isset($links[0]) ? count($links[0]) : 0;

		if (!$has_search && $link_count < 3) {
			return [
				'id' => 'seo-404-page-not-helpful',
				'title' => '404 Page Not Helpful',
				'description' => '404 page exists but lacks helpful elements (search box, navigation links). Help users find what they\'re looking for.'
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
		return __('Helpful 404 Page', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks that the 404 page offers navigation or search.', 'wpshadow');
	}
}

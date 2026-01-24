<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Internal_Links extends Diagnostic_Base
{

	protected static $slug = 'test-seo-internal-links';
	protected static $title = 'Internal Linking Test';
	protected static $description = 'Tests for adequate internal linking';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		if ($html !== null) {
			return self::analyze_html($html, $url ?? 'provided-html');
		}

		$html = self::fetch_html($url ?? home_url('/'));
		if ($html === false) {
			return null;
		}

		return self::analyze_html($html, $url ?? home_url('/'));
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		$site_url = home_url('/');

		// Count internal links
		preg_match_all('/<a[^>]+href=["\']([^"\']+)["\']/i', $html, $all_links);
		$total_links = count($all_links[1]);

		$internal_links = 0;
		$external_links = 0;

		foreach ($all_links[1] as $link) {
			if (strpos($link, $site_url) === 0 || strpos($link, '/') === 0) {
				$internal_links++;
			} elseif (preg_match('/^https?:\/\//i', $link)) {
				$external_links++;
			}
		}

		if ($total_links > 0 && $internal_links < 3) {
			return [
				'id' => 'seo-internal-links-low',
				'title' => 'Low Internal Linking',
				'description' => sprintf('Only %d internal links found. Internal linking helps SEO by distributing page authority and helping search engines discover content.', $internal_links)
				'kb_link' => 'https://wpshadow.com/kb/internal-linking/',
				'training_link' => 'https://wpshadow.com/training/link-building/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => ['internal_links' => $internal_links, 'external_links' => $external_links, 'total_links' => $total_links],
			];
		}

		return null;
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Internal Linking', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for adequate internal linking.', 'wpshadow');
	}
}

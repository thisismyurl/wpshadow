<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Broken_Links extends Diagnostic_Base
{

	protected static $slug = 'test-content-broken-links';
	protected static $title = 'Broken Links Test';
	protected static $description = 'Tests for broken internal links (404s)';

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
		$site_domain = parse_url($site_url, PHP_URL_HOST);

		// Find all internal links
		preg_match_all('/<a[^>]+href=["\']([^"\']+)["\']/i', $html, $links);

		$internal_links = [];
		foreach ($links[1] as $link) {
			$link_domain = parse_url($link, PHP_URL_HOST);

			// Check if internal link
			if (
				$link_domain === $site_domain ||
				($link_domain === null && strpos($link, '/') === 0)
			) {

				// Make absolute if relative
				if (strpos($link, 'http') !== 0) {
					$link = rtrim($site_url, '/') . '/' . ltrim($link, '/');
				}

				$internal_links[] = $link;
			}
		}

		if (empty($internal_links)) {
			return null;
		}

		// Sample check (check first 5 links to avoid long execution)
		$broken_links = [];
		$check_count = min(5, count($internal_links));

		for ($i = 0; $i < $check_count; $i++) {
			$link = $internal_links[$i];
			$response = wp_remote_head($link, ['timeout' => 5, 'sslverify' => false]);

			if (is_wp_error($response)) {
				continue;
			}

			$status_code = wp_remote_retrieve_response_code($response);
			if ($status_code === 404 || $status_code >= 500) {
				$broken_links[] = $link;
			}
		}

		if (!empty($broken_links)) {
			return [
				'id' => 'content-broken-links',
				'title' => 'Broken Links Detected',
				'description' => sprintf(
					'%d broken link(s) found in sample of %d. Broken links hurt SEO, UX, and credibility.',
					count($broken_links),
					$check_count
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/broken-links/',
				'training_link' => 'https://wpshadow.com/training/link-maintenance/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'module' => 'Content Quality',
				'priority' => 2,
				'meta' => ['broken_count' => count($broken_links), 'checked_count' => $check_count, 'broken_links' => $broken_links],
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
		return __('Broken Links', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for broken internal links (404s).', 'wpshadow');
	}
}

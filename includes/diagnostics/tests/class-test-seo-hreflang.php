<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Hreflang extends Diagnostic_Base
{

	protected static $slug = 'test-seo-hreflang';
	protected static $title = 'Hreflang Test';
	protected static $description = 'Tests for hreflang alternate language links';

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
		// Check for hreflang links
		preg_match_all('/<link[^>]+hreflang=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
		$hreflang_count = count($matches[1]);

		if ($hreflang_count === 0) {
			// Only report if site has multilingual indicators
			$has_lang_switcher = preg_match('/lang-|language-|wpml-|polylang/i', $html);
			if (!$has_lang_switcher) {
				return null; // Not multilingual
			}

			return [
				'id' => 'seo-hreflang',
				'title' => 'Missing Hreflang Tags',
				'description' => 'This appears to be a multilingual site but lacks hreflang tags. Hreflang helps search engines serve the correct language version to users.'
				'kb_link' => 'https://wpshadow.com/kb/hreflang/',
				'training_link' => 'https://wpshadow.com/training/international-seo/',
				'auto_fixable' => false,
				'threat_level' => 50,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => ['checked_url' => $checked_url],
			];
		}

		// Check for self-referencing hreflang
		$parsed_url = wp_parse_url($checked_url);
		$has_self_reference = false;

		foreach ($matches[0] as $link) {
			if (strpos($link, $parsed_url['path'] ?? '/') !== false) {
				$has_self_reference = true;
				break;
			}
		}

		if (!$has_self_reference) {
			return [
				'id' => 'seo-hreflang',
				'title' => 'Incomplete Hreflang Implementation',
				'description' => 'Hreflang tags found but missing self-referencing link. Each page should include hreflang pointing to itself.'
				'kb_link' => 'https://wpshadow.com/kb/hreflang/',
				'training_link' => 'https://wpshadow.com/training/international-seo/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => [
					'hreflang_count' => $hreflang_count,
					'checked_url' => $checked_url,
				],
			];
		}

		return null; // PASS - has proper hreflang
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Hreflang Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for hreflang tags on multilingual sites.', 'wpshadow');
	}
}

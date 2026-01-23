<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Related_Posts extends Diagnostic_Base
{

	protected static $slug = 'test-content-related-posts';
	protected static $title = 'Related Posts Test';
	protected static $description = 'Tests for related/recommended content';

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
		// Check if it's a blog post/article
		$is_article = (preg_match('/<article[^>]*>/i', $html) ||
			preg_match('/class=["\'][^"\']*(?:single|post|entry|article)[^"\']*["\']/i', $html));

		if (!$is_article) {
			return null;
		}

		// Look for related posts indicators
		$related_patterns = [
			'/related\s+(?:posts|articles|content)/i',
			'/you\s+(?:may|might)\s+(?:also\s+)?like/i',
			'/recommended\s+(?:for\s+you|posts|articles)/i',
			'/similar\s+(?:posts|articles)/i',
			'/class=["\'][^"\']*(?:related|recommended)[^"\']*["\']/i'
		];

		$has_related = false;
		foreach ($related_patterns as $pattern) {
			if (preg_match($pattern, $html)) {
				$has_related = true;
				break;
			}
		}

		if ($is_article && !$has_related) {
			return [
				'id' => 'content-no-related-posts',
				'title' => 'No Related Posts Section',
				'description' => 'Article without related posts. Related content can reduce bounce rate by 10-20% and increase pageviews per session.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/related-posts/',
				'training_link' => 'https://wpshadow.com/training/content-engagement/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['has_related' => false],
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
		return __('Related Posts', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for related/recommended content.', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Social_Sharing extends Diagnostic_Base
{

	protected static $slug = 'test-content-social-sharing';
	protected static $title = 'Social Sharing Test';
	protected static $description = 'Tests for social sharing buttons';

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
			preg_match('/class=["\'][^"\']*(?:post|entry|article)[^"\']*["\']/i', $html));

		if (!$is_article) {
			return null;
		}

		// Look for social sharing indicators
		$sharing_patterns = [
			'/share|social/i',
			'/twitter\.com\/intent\/tweet/i',
			'/facebook\.com\/sharer/i',
			'/linkedin\.com\/sharing/i',
			'/pinterest\.com\/pin/i',
			'/class=["\'][^"\']*(?:share|social)[^"\']*["\']/i'
		];

		$has_sharing = false;
		foreach ($sharing_patterns as $pattern) {
			if (preg_match($pattern, $html)) {
				$has_sharing = true;
				break;
			}
		}

		if ($is_article && !$has_sharing) {
			return [
				'id' => 'content-no-social-sharing',
				'title' => 'No Social Sharing Buttons',
				'description' => 'Article content without social sharing buttons. Easy sharing can increase organic reach by 20-40%.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/social-sharing/',
				'training_link' => 'https://wpshadow.com/training/content-distribution/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['has_sharing' => false],
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
		return __('Social Sharing', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for social sharing buttons.', 'wpshadow');
	}
}

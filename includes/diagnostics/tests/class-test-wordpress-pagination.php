<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Pagination extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-pagination';
	protected static $title = 'Pagination Implementation Test';
	protected static $description = 'Tests for proper pagination on archive pages';

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
		// Check if it's an archive page (multiple posts)
		preg_match_all('/<article[^>]*>/i', $html, $articles);
		$article_count = count($articles[0]);

		if ($article_count < 5) {
			return null; // Not enough posts for pagination concern
		}

		// Check for pagination indicators
		$has_pagination = preg_match('/<nav[^>]+class=["\'][^"\']*(?:pagination|paging)[^"\']*["\']/i', $html) ||
			preg_match('/class=["\'][^"\']*(?:page-numbers|pagination)[^"\']*["\']/i', $html) ||
			preg_match('/<a[^>]+rel=["\'](?:next|prev)["\']/i', $html);

		if (!$has_pagination) {
			return [
				'id' => 'wordpress-no-pagination',
				'title' => 'No Pagination Detected',
				'description' => sprintf('%d articles found but no pagination navigation. Long pages hurt performance and UX.', $article_count),
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/pagination/',
				'training_link' => 'https://wpshadow.com/training/archive-optimization/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'WordPress',
				'priority' => 3,
				'meta' => ['article_count' => $article_count, 'has_pagination' => false],
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
		return __('Pagination Implementation', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for proper pagination on archive pages.', 'wpshadow');
	}
}

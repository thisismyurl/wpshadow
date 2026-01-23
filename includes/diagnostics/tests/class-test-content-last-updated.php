<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Last_Updated extends Diagnostic_Base
{

	protected static $slug = 'test-content-last-updated';
	protected static $title = 'Content Freshness Test';
	protected static $description = 'Tests for content last updated date display';

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
		// Check if it's a blog post/article (has article tag or post content indicators)
		$is_article = (preg_match('/<article[^>]*>/i', $html) ||
			preg_match('/class=["\'][^"\']*(?:post|entry|article)[^"\']*["\']/i', $html));

		if (!$is_article) {
			return null; // Not applicable for non-article pages
		}

		// Look for last updated/modified date indicators
		$has_updated_date = (
			preg_match('/(?:last\s*)?updated[:\s]/i', $html) ||
			preg_match('/(?:last\s*)?modified[:\s]/i', $html) ||
			preg_match('/<time[^>]+class=["\'][^"\']*(?:updated|modified)[^"\']*["\']/i', $html) ||
			preg_match('/class=["\'][^"\']*date[_-]?(?:updated|modified)[^"\']*["\']/i', $html)
		);

		// Look for published date
		$has_published_date = (
			preg_match('/<time[^>]+datetime=/i', $html) ||
			preg_match('/published[:\s]/i', $html) ||
			preg_match('/class=["\'][^"\']*(?:published|post-date|entry-date)[^"\']*["\']/i', $html)
		);

		if ($is_article && !$has_updated_date && !$has_published_date) {
			return [
				'id' => 'content-no-dates',
				'title' => 'No Publication/Update Dates',
				'description' => 'Article content detected but no published or updated dates shown. Date transparency builds trust and helps SEO.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/content-dates/',
				'training_link' => 'https://wpshadow.com/training/content-strategy/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['has_dates' => false],
			];
		}

		if ($is_article && $has_published_date && !$has_updated_date) {
			return [
				'id' => 'content-no-updated-date',
				'title' => 'Missing "Last Updated" Date',
				'description' => 'Published date shown but no "last updated" date. Showing update dates signals fresh content to users and search engines.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/last-updated-dates/',
				'training_link' => 'https://wpshadow.com/training/content-freshness/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['has_published' => true, 'has_updated' => false],
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
		return __('Content Freshness', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for content last updated date display.', 'wpshadow');
	}
}

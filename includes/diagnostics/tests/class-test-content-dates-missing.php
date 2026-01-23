<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Dates_Missing extends Diagnostic_Base
{

	protected static $slug = 'test-content-dates-missing';
	protected static $title = 'Publication/Update Dates Missing Test';
	protected static $description = 'Tests for missing published or updated dates on articles.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$body = $html ?? self::fetch_html($url ?? home_url('/'));
		if ($body === false) {
			return null;
		}

		if (!self::is_article($body)) {
			return null;
		}

		$has_updated_date = self::has_updated_date($body);
		$has_published_date = self::has_published_date($body);

		if (!$has_updated_date && !$has_published_date) {
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

		return null;
	}

	protected static function is_article(string $html): bool
	{
		return (bool) (preg_match('/<article[^>]*>/i', $html) ||
			preg_match('/class=["\'][^"\']*(?:post|entry|article)[^"\']*["\']/i', $html));
	}

	protected static function has_updated_date(string $html): bool
	{
		return (bool) (
			preg_match('/(?:last\s*)?updated[:\s]/i', $html) ||
			preg_match('/(?:last\s*)?modified[:\s]/i', $html) ||
			preg_match('/<time[^>]+class=["\'][^"\']*(?:updated|modified)[^"\']*["\']/i', $html) ||
			preg_match('/class=["\'][^"\']*date[_-]?(?:updated|modified)[^"\']*["\']/i', $html)
		);
	}

	protected static function has_published_date(string $html): bool
	{
		return (bool) (
			preg_match('/<time[^>]+datetime=/i', $html) ||
			preg_match('/published[:\s]/i', $html) ||
			preg_match('/class=["\'][^"\']*(?:published|post-date|entry-date)[^"\']*["\']/i', $html)
		);
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Publication/Update Dates Missing', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks if article pages show any publish or update dates.', 'wpshadow');
	}
}

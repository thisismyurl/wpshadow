<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Search_Functionality extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-search-functionality';
	protected static $title = 'Search Functionality Test';
	protected static $description = 'Tests for site search functionality';

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
		// Check for search form
		$has_search = preg_match('/<form[^>]+role=["\']search["\']/i', $html) ||
			preg_match('/<input[^>]+type=["\']search["\']/i', $html) ||
			preg_match('/<input[^>]+name=["\']s["\']/i', $html);

		if (!$has_search) {
			return [
				'id' => 'wordpress-no-search',
				'title' => 'No Search Functionality',
				'description' => 'No search form detected. Site search improves user experience and helps visitors find content quickly.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/site-search/',
				'training_link' => 'https://wpshadow.com/training/user-experience/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'WordPress',
				'priority' => 3,
				'meta' => ['has_search' => false],
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
		return __('Search Functionality', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for site search functionality.', 'wpshadow');
	}
}

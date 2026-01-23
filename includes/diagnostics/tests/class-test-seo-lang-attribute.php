<?php

declare(strict_types=1);
/**
 * Test: HTML Lang Attribute Check
 *
 * Tests for proper HTML lang attribute for language declaration.
 *
 * Philosophy: Accessibility (#8) - Essential for screen readers
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Lang_Attribute extends Diagnostic_Base
{

	protected static $slug = 'test-seo-lang-attribute';
	protected static $title = 'HTML Lang Attribute Test';
	protected static $description = 'Tests for HTML lang attribute presence';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		if ($html !== null) {
			return self::analyze_html($html, $url ?? 'provided-html');
		}

		$site_url = $url ?? home_url('/');

		if ($url !== null && !self::is_internal_url($url)) {
			return self::error_result('Invalid URL', 'URL must be from this WordPress site');
		}

		$html = self::fetch_html($site_url);
		if ($html === false) {
			return self::error_result('Fetch Failed', 'Could not retrieve page HTML');
		}

		return self::analyze_html($html, $site_url);
	}

	public static function run_lang_tests(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		if ($html === false) {
			return ['success' => false, 'error' => 'Could not fetch HTML'];
		}

		$lang = self::extract_lang($html);

		return [
			'success' => true,
			'lang_attribute' => $lang,
			'tests' => [
				'has_lang' => self::test_has_lang($html),
				'valid_format' => self::test_valid_format($html),
				'not_empty' => self::test_not_empty($html),
			],
		];
	}

	public static function test_has_lang(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$lang = self::extract_lang($html);

		return [
			'test' => 'has_lang',
			'passed' => !empty($lang),
			'value' => $lang,
			'message' => !empty($lang) ? 'HTML lang attribute present' : 'Missing HTML lang attribute',
			'impact' => 'Lang attribute helps screen readers pronounce content correctly',
		];
	}

	public static function test_valid_format(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$lang = self::extract_lang($html);

		// Valid format: 2-letter code or 2-letter + region (en, en-US, etc.)
		$is_valid = preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $lang);

		return [
			'test' => 'valid_format',
			'passed' => $is_valid,
			'value' => $lang,
			'message' => $is_valid ? 'Lang attribute format valid' : 'Lang attribute format invalid',
			'impact' => 'Invalid format may not be recognized by browsers',
		];
	}

	public static function test_not_empty(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$lang = self::extract_lang($html);

		return [
			'test' => 'not_empty',
			'passed' => !empty($lang),
			'value' => $lang,
			'message' => !empty($lang) ? 'Lang attribute has value' : 'Lang attribute empty',
			'impact' => 'Empty lang attribute provides no language information',
		];
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		$lang = self::extract_lang($html);

		if (empty($lang)) {
			return [
				'id' => 'seo-lang-attribute',
				'title' => 'Missing HTML Lang Attribute',
				'description' => 'The HTML element is missing a lang attribute. This attribute helps screen readers and search engines understand the page language.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/html-lang/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/accessibility-basics/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => ['checked_url' => $checked_url],
			];
		}

		return null; // PASS
	}

	protected static function extract_lang(string $html): string
	{
		if (preg_match('/<html[^>]+lang=["\']([^"\']+)["\']/i', $html, $match)) {
			return trim($match[1]);
		}
		return '';
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, [
			'timeout' => 10,
			'user-agent' => 'WPShadow-Diagnostic/1.0',
			'sslverify' => false,
		]);

		if (is_wp_error($response)) {
			return false;
		}

		return wp_remote_retrieve_body($response);
	}

	protected static function is_internal_url(string $url): bool
	{
		$site_host = wp_parse_url(home_url(), PHP_URL_HOST);
		$test_host = wp_parse_url($url, PHP_URL_HOST);
		return $site_host === $test_host;
	}

	protected static function error_result(string $title, string $description): array
	{
		return [
			'id' => 'seo-lang-attribute',
			'title' => $title,
			'description' => $description,
			'color' => '#ff5722',
			'bg_color' => '#ffebee',
			'kb_link' => 'https://wpshadow.com/kb/html-lang/',
			'training_link' => 'https://wpshadow.com/training/accessibility-basics/',
			'auto_fixable' => false,
			'threat_level' => 30,
			'module' => 'SEO',
			'priority' => 3,
		];
	}

	public static function get_name(): string
	{
		return __('HTML Lang Attribute', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for HTML lang attribute (accessibility & SEO).', 'wpshadow');
	}
}

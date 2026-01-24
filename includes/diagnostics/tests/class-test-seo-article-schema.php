<?php

declare(strict_types=1);
/**
 * Test: Article Schema Check
 *
 * Tests for Article/BlogPosting schema markup.
 *
 * Philosophy: Show value (#9) - Rich snippets increase CTR
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Article_Schema extends Diagnostic_Base
{

	protected static $slug = 'test-seo-article-schema';
	protected static $title = 'Article Schema Test';
	protected static $description = 'Tests for Article/BlogPosting schema';

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

	public static function run_article_schema_tests(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		if ($html === false) {
			return ['success' => false, 'error' => 'Could not fetch HTML'];
		}

		$schema = self::extract_article_schema($html);

		return [
			'success' => true,
			'article_schema' => $schema,
			'tests' => [
				'has_article_schema' => self::test_has_article_schema($html),
				'has_headline' => self::test_has_headline($html),
				'has_author' => self::test_has_author($html),
				'has_date_published' => self::test_has_date_published($html),
				'has_image' => self::test_has_image($html),
			],
		];
	}

	public static function test_has_article_schema(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_schema = preg_match('/"@type"\s*:\s*"(Article|BlogPosting|NewsArticle)"/i', $html);

		return [
			'test' => 'has_article_schema',
			'passed' => $has_schema,
			'message' => $has_schema ? 'Article schema found' : 'No Article schema',
			'impact' => 'Article schema enables rich snippets in search results',
		];
	}

	public static function test_has_headline(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_headline = preg_match('/"headline"\s*:\s*"[^"]+"/i', $html);

		return [
			'test' => 'has_headline',
			'passed' => $has_headline,
			'message' => $has_headline ? 'Headline property present' : 'Missing headline property',
			'impact' => 'Headline is required for Article schema',
		];
	}

	public static function test_has_author(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_author = preg_match('/"author"\s*:\s*{/i', $html);

		return [
			'test' => 'has_author',
			'passed' => $has_author,
			'message' => $has_author ? 'Author property present' : 'Missing author property',
			'impact' => 'Author information builds credibility',
		];
	}

	public static function test_has_date_published(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_date = preg_match('/"datePublished"\s*:\s*"[^"]+"/i', $html);

		return [
			'test' => 'has_date_published',
			'passed' => $has_date,
			'message' => $has_date ? 'Date published present' : 'Missing date published',
			'impact' => 'Publication date is required for Article schema',
		];
	}

	public static function test_has_image(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_image = preg_match('/"image"\s*:\s*["\[{]/i', $html);

		return [
			'test' => 'has_image',
			'passed' => $has_image,
			'message' => $has_image ? 'Image property present' : 'Missing image property',
			'impact' => 'Images enhance article appearance in search results',
		];
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		// Check if this looks like an article/post page
		$is_single = is_single() || is_singular('post');

		if (!$is_single && !preg_match('/<article[\s>]/i', $html)) {
			return null; // Not an article page, skip test
		}

		$has_article_schema = preg_match('/"@type"\s*:\s*"(Article|BlogPosting|NewsArticle)"/i', $html);

		if ($has_article_schema) {
			// Check for required properties
			$has_headline = preg_match('/"headline"\s*:\s*"[^"]+"/i', $html);
			$has_author = preg_match('/"author"\s*:\s*{/i', $html);
			$has_date = preg_match('/"datePublished"\s*:\s*"[^"]+"/i', $html);

			$missing = [];
			if (!$has_headline) $missing[] = 'headline';
			if (!$has_author) $missing[] = 'author';
			if (!$has_date) $missing[] = 'datePublished';

			if (!empty($missing)) {
				return [
					'id' => 'seo-article-schema',
					'title' => 'Incomplete Article Schema',
					'description' => sprintf(
						'Article schema is present but missing required properties: %s. Complete schema is needed for rich snippets.',
						implode(', ', $missing)
					)
					'kb_link' => 'https://wpshadow.com/kb/article-schema/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
					'training_link' => 'https://wpshadow.com/training/structured-data/',
					'auto_fixable' => false,
					'threat_level' => 40,
					'module' => 'SEO',
					'priority' => 2,
					'meta' => [
						'missing_properties' => $missing,
						'checked_url' => $checked_url,
					],
				];
			}

			return null; // PASS - complete article schema
		}

		// Missing article schema on article page
		return [
			'id' => 'seo-article-schema',
			'title' => 'Missing Article Schema',
			'description' => 'This appears to be an article/blog post but lacks Article schema markup. Adding Article schema enables rich snippets in search results, increasing click-through rates.'
			'kb_link' => 'https://wpshadow.com/kb/article-schema/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/structured-data/',
			'auto_fixable' => false,
			'threat_level' => 50,
			'module' => 'SEO',
			'priority' => 2,
			'meta' => [
				'issue' => 'missing',
				'checked_url' => $checked_url,
			],
		];
	}

	protected static function extract_article_schema(string $html): array
	{
		if (empty($html) || !preg_match('/"@type"\s*:\s*"(Article|BlogPosting|NewsArticle)"/i', $html)) {
			return [];
		}

		$schema = [];

		if (preg_match('/"headline"\s*:\s*"([^"]+)"/i', $html, $match)) {
			$schema['headline'] = $match[1];
		}
		if (preg_match('/"author"\s*:\s*{[^}]*"name"\s*:\s*"([^"]+)"/i', $html, $match)) {
			$schema['author'] = $match[1];
		}
		if (preg_match('/"datePublished"\s*:\s*"([^"]+)"/i', $html, $match)) {
			$schema['datePublished'] = $match[1];
		}

		return $schema;
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
			'id' => 'seo-article-schema',
			'title' => $title,
			'description' => $description
			'kb_link' => 'https://wpshadow.com/kb/article-schema/',
			'training_link' => 'https://wpshadow.com/training/structured-data/',
			'auto_fixable' => false,
			'threat_level' => 40,
			'module' => 'SEO',
			'priority' => 3,
		];
	}

	public static function get_name(): string
	{
		return __('Article Schema Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for Article/BlogPosting schema on blog posts.', 'wpshadow');
	}
}

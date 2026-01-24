<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_FAQ_Schema extends Diagnostic_Base
{

	protected static $slug = 'test-seo-faq-schema';
	protected static $title = 'FAQ Schema Test';
	protected static $description = 'Tests for FAQ schema markup';

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
		$has_faq = preg_match('/"@type"\s*:\s*"FAQPage"/i', $html);

		if (!$has_faq) {
			// Only report if page has Q&A pattern
			$has_questions = preg_match_all('/<h[2-6][^>]*>.*?\?.*?<\/h[2-6]>/i', $html);
			if ($has_questions < 3) {
				return null; // Not enough Q&A content
			}

			return [
				'id' => 'seo-faq-schema',
				'title' => 'Missing FAQ Schema',
				'description' => 'This page contains Q&A content but lacks FAQ schema markup. Adding FAQ schema can generate rich snippets in search results.'
				'kb_link' => 'https://wpshadow.com/kb/faq-schema/',
				'training_link' => 'https://wpshadow.com/training/structured-data/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => ['checked_url' => $checked_url],
			];
		}

		return null; // Has FAQ schema
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('FAQ Schema Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for FAQ schema on Q&A pages.', 'wpshadow');
	}
}

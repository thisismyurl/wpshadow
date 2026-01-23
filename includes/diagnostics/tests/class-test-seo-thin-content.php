<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Thin_Content extends Diagnostic_Base
{

	protected static $slug = 'test-seo-thin-content';
	protected static $title = 'Thin Content Test';
	protected static $description = 'Tests for insufficient content length';

	const MIN_WORD_COUNT = 300;
	const IDEAL_WORD_COUNT = 600;

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
		// Extract main content text (strip scripts, styles, nav, footer)
		$content = preg_replace('/<(script|style|nav|header|footer|aside)[^>]*>.*?<\/\1>/is', '', $html);

		// Strip HTML tags
		$text = strip_tags($content);

		// Remove extra whitespace
		$text = preg_replace('/\s+/', ' ', $text);

		// Count words
		$word_count = str_word_count($text);

		if ($word_count >= self::IDEAL_WORD_COUNT) {
			return null; // PASS - good content length
		}

		if ($word_count >= self::MIN_WORD_COUNT) {
			return null; // Acceptable
		}

		$threat_level = 50;
		if ($word_count < 100) {
			$threat_level = 70; // Very thin
		}

		return [
			'id' => 'seo-thin-content',
			'title' => 'Thin Content Detected',
			'description' => sprintf(
				'This page has only %d words. Thin content (under %d words) typically underperforms in search. Consider expanding with valuable, relevant information.',
				$word_count,
				self::MIN_WORD_COUNT
			),
			'color' => '#ff9800',
			'bg_color' => '#fff3e0',
			'kb_link' => 'https://wpshadow.com/kb/thin-content/',
			'training_link' => 'https://wpshadow.com/training/content-strategy/',
			'auto_fixable' => false,
			'threat_level' => $threat_level,
			'module' => 'SEO',
			'priority' => 2,
			'meta' => [
				'word_count' => $word_count,
				'min_recommended' => self::MIN_WORD_COUNT,
				'ideal' => self::IDEAL_WORD_COUNT,
				'checked_url' => $checked_url,
			],
		];
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Thin Content Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for insufficient content length (under 300 words).', 'wpshadow');
	}
}

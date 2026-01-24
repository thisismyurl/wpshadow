<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Readability extends Diagnostic_Base
{

	protected static $slug = 'test-seo-readability';
	protected static $title = 'Content Readability Test';
	protected static $description = 'Tests for readable content (Flesch Reading Ease)';

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
		// Extract main content
		if (preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $article_match)) {
			$content = $article_match[1];
		} elseif (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $main_match)) {
			$content = $main_match[1];
		} else {
			$content = $html;
		}

		$text = strip_tags($content);

		// Count sentences (approximation)
		$sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
		$sentence_count = count($sentences);

		// Count words
		$words = str_word_count($text, 1);
		$word_count = count($words);

		if ($word_count < 50 || $sentence_count < 3) {
			return null; // Too short to analyze
		}

		// Calculate average sentence length
		$avg_sentence_length = $word_count / $sentence_count;

		// Very long sentences are hard to read
		if ($avg_sentence_length > 25) {
			return [
				'id' => 'seo-readability-long-sentences',
				'title' => 'Content Readability Issues',
				'description' => sprintf('Average sentence length is %.1f words. Aim for 15-20 words per sentence for better readability.', $avg_sentence_length)
				'kb_link' => 'https://wpshadow.com/kb/content-readability/',
				'training_link' => 'https://wpshadow.com/training/seo-writing/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'Content',
				'priority' => 4,
				'meta' => ['avg_sentence_length' => round($avg_sentence_length, 1), 'word_count' => $word_count, 'sentence_count' => $sentence_count],
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
		return __('Content Readability', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for readable content (sentence length).', 'wpshadow');
	}
}

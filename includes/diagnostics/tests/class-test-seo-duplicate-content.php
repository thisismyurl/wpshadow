<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Duplicate_Content extends Diagnostic_Base
{

	protected static $slug = 'test-seo-duplicate-content';
	protected static $title = 'Duplicate Content Test';
	protected static $description = 'Tests for duplicate content indicators';

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
		$issues = [];

		// Check for multiple H1s (often sign of duplicate sections)
		preg_match_all('/<h1[^>]*>/i', $html, $h1_matches);
		if (count($h1_matches[0]) > 1) {
			$issues[] = sprintf('%d H1 tags (should have only one)', count($h1_matches[0]));
		}

		// Check for repeated paragraphs (exact duplicates)
		preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $paragraphs);
		$p_texts = array_map('trim', $p_texts = array_map('strip_tags', $paragraphs[1]));
		$unique_p = array_unique($p_texts);

		if (count($p_texts) - count($unique_p) > 3) {
			$duplicate_count = count($p_texts) - count($unique_p);
			$issues[] = sprintf('%d duplicate paragraphs detected', $duplicate_count);
		}

		// Check for URL parameters (common duplicate content cause)
		$parsed = wp_parse_url($checked_url);
		if (isset($parsed['query'])) {
			parse_str($parsed['query'], $params);
			// Filter out known safe parameters
			$safe_params = ['page', 'p', 'preview', 'preview_id'];
			$risky_params = array_diff(array_keys($params), $safe_params);

			if (!empty($risky_params)) {
				$issues[] = sprintf('URL parameters may cause duplication: %s', implode(', ', $risky_params));
			}
		}

		if (empty($issues)) {
			return null; // PASS
		}

		$threat_level = 40;
		if (count($issues) > 2) {
			$threat_level = 60;
		}

		return [
			'id' => 'seo-duplicate-content',
			'title' => 'Duplicate Content Indicators',
			'description' => sprintf(
				'Detected %d potential duplicate content issues: %s. Duplicate content can dilute search rankings.',
				count($issues),
				implode('; ', array_slice($issues, 0, 3))
			),
			'color' => '#ff9800',
			'bg_color' => '#fff3e0',
			'kb_link' => 'https://wpshadow.com/kb/duplicate-content/',
			'training_link' => 'https://wpshadow.com/training/content-strategy/',
			'auto_fixable' => false,
			'threat_level' => $threat_level,
			'module' => 'SEO',
			'priority' => 2,
			'meta' => [
				'issues' => $issues,
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
		return __('Duplicate Content Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Detects duplicate content indicators (multiple H1s, repeated text).', 'wpshadow');
	}
}

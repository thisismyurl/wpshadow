<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Semantic_HTML extends Diagnostic_Base
{

	protected static $slug = 'test-ux-semantic-html';
	protected static $title = 'Semantic HTML Test';
	protected static $description = 'Tests for semantic HTML5 elements usage';

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
		// Check for semantic HTML5 elements
		$has_header = preg_match('/<header[^>]*>/i', $html);
		$has_nav = preg_match('/<nav[^>]*>/i', $html);
		$has_main = preg_match('/<main[^>]*>/i', $html);
		$has_article = preg_match('/<article[^>]*>/i', $html);
		$has_footer = preg_match('/<footer[^>]*>/i', $html);

		// Count divs (over-reliance on divs is an anti-pattern)
		preg_match_all('/<div[^>]*>/i', $html, $div_matches);
		$div_count = count($div_matches[0]);

		$semantic_score = 0;
		if ($has_header) $semantic_score++;
		if ($has_nav) $semantic_score++;
		if ($has_main) $semantic_score++;
		if ($has_article) $semantic_score++;
		if ($has_footer) $semantic_score++;

		// If many divs but low semantic score
		if ($div_count > 20 && $semantic_score < 3) {
			return [
				'id' => 'ux-semantic-html-low',
				'title' => 'Low Semantic HTML Usage',
				'description' => sprintf(
					'Found %d divs but only %d/5 semantic HTML5 elements (header, nav, main, article, footer). Semantic HTML improves accessibility and SEO.',
					$div_count,
					$semantic_score
				)
				'kb_link' => 'https://wpshadow.com/kb/semantic-html/',
				'training_link' => 'https://wpshadow.com/training/html-best-practices/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => [
					'div_count' => $div_count,
					'semantic_score' => $semantic_score,
					'has_header' => $has_header,
					'has_nav' => $has_nav,
					'has_main' => $has_main,
					'has_article' => $has_article,
					'has_footer' => $has_footer,
					'checked_url' => $checked_url,
				],
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
		return __('Semantic HTML', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for semantic HTML5 element usage vs div soup.', 'wpshadow');
	}
}

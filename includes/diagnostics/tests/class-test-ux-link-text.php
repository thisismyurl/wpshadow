<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Link_Text extends Diagnostic_Base
{

	protected static $slug = 'test-ux-link-text';
	protected static $title = 'Link Text Descriptiveness Test';
	protected static $description = 'Tests for descriptive link text (avoid "click here")';

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
		// Find all links with text
		preg_match_all('/<a[^>]*>(.*?)<\/a>/is', $html, $link_matches);

		$total_links = count($link_matches[1]);
		if ($total_links === 0) {
			return null;
		}

		// Bad link text patterns
		$bad_patterns = [
			'click here',
			'read more',
			'more',
			'here',
			'link',
			'this',
			'continue',
		];

		$bad_links = 0;
		$examples = [];

		foreach ($link_matches[1] as $link_text) {
			$clean_text = strtolower(strip_tags(trim($link_text)));

			if (empty($clean_text)) {
				$bad_links++; // Empty link text
				continue;
			}

			foreach ($bad_patterns as $pattern) {
				if ($clean_text === $pattern) {
					$bad_links++;
					if (count($examples) < 3) {
						$examples[] = $clean_text;
					}
					break;
				}
			}
		}

		$percentage = ($bad_links / $total_links) * 100;

		if ($percentage > 20) { // More than 20% generic links
			return [
				'id' => 'ux-link-text-generic',
				'title' => 'Generic Link Text Detected',
				'description' => sprintf(
					'%d of %d links (%.1f%%) use generic text like "click here" or "read more". Descriptive link text improves accessibility and SEO. Examples: %s',
					$bad_links,
					$total_links,
					$percentage,
					implode(', ', array_slice($examples, 0, 3))
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/link-text-best-practices/',
				'training_link' => 'https://wpshadow.com/training/content-ux/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'UX',
				'priority' => 2,
				'meta' => [
					'bad_links' => $bad_links,
					'total_links' => $total_links,
					'percentage' => round($percentage, 1),
					'examples' => $examples,
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
		return __('Link Text Descriptiveness', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for descriptive link text (UX & accessibility).', 'wpshadow');
	}
}

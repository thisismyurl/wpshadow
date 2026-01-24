<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Table_Of_Contents extends Diagnostic_Base
{

	protected static $slug = 'test-content-table-of-contents';
	protected static $title = 'Table of Contents Test';
	protected static $description = 'Tests for table of contents on long-form content';

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
		// Count headings (h2-h4)
		preg_match_all('/<h[2-4][^>]*>/', $html, $headings);
		$heading_count = count($headings[0]);

		// Long-form content = 5+ subheadings
		if ($heading_count < 5) {
			return null;
		}

		// Look for table of contents
		$toc_patterns = [
			'/table\s+of\s+contents/i',
			'/class=["\'][^"\']*(?:toc|table[_-]?of[_-]?contents)[^"\']*["\']/i',
			'/id=["\'](?:toc|table[_-]?of[_-]?contents)["\']/i',
			'/<nav[^>]+aria-label=["\']table of contents["\']/i'
		];

		$has_toc = false;
		foreach ($toc_patterns as $pattern) {
			if (preg_match($pattern, $html)) {
				$has_toc = true;
				break;
			}
		}

		if (!$has_toc) {
			return [
				'id' => 'content-no-table-of-contents',
				'title' => 'Missing Table of Contents',
				'description' => sprintf('Long-form content (%d headings) without table of contents. ToC improves navigation, UX, and can earn jump links in search results.', $heading_count)
				'kb_link' => 'https://wpshadow.com/kb/table-of-contents/',
				'training_link' => 'https://wpshadow.com/training/content-structure/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['heading_count' => $heading_count],
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
		return __('Table of Contents', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for table of contents on long-form content.', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Inline_CSS_Size extends Diagnostic_Base
{

	protected static $slug = 'test-performance-inline-css-size';
	protected static $title = 'Inline CSS Size Test';
	protected static $description = 'Tests for excessive inline CSS size (>50KB).';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$body = $html ?? self::fetch_html($url ?? home_url('/'));
		if ($body === false) {
			return null;
		}

		preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $body, $inline_styles);
		$total_inline_css_size = 0;

		foreach ($inline_styles[1] as $style_content) {
			$total_inline_css_size += strlen($style_content);
		}

		if ($total_inline_css_size > 51200) {
			return [
				'id' => 'performance-excessive-inline-css',
				'title' => 'Excessive Inline CSS',
				'description' => sprintf(
					'%.1f KB of inline CSS detected. Large inline styles bloat HTML. Consider external stylesheets with caching.',
					$total_inline_css_size / 1024
				)
				'kb_link' => 'https://wpshadow.com/kb/inline-css/',
				'training_link' => 'https://wpshadow.com/training/asset-optimization/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['inline_css_size_kb' => round($total_inline_css_size / 1024, 1), 'inline_style_count' => count($inline_styles[0])],
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
		return __('Inline CSS Size', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks total inline CSS size on the page.', 'wpshadow');
	}
}

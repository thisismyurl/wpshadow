<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Excessive_CSS extends Diagnostic_Base
{

	protected static $slug = 'test-performance-excessive-css';
	protected static $title = 'Excessive CSS Test';
	protected static $description = 'Tests for too many CSS files or excessive inline styles';

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
		// Count CSS files
		preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $stylesheets);
		$css_file_count = count($stylesheets[0]);

		// Count inline styles
		preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $inline_styles);
		$inline_style_count = count($inline_styles[0]);

		// Measure total inline CSS size
		$total_inline_css_size = 0;
		foreach ($inline_styles[1] as $style_content) {
			$total_inline_css_size += strlen($style_content);
		}

		if ($css_file_count > 10) {
			return [
				'id' => 'performance-excessive-css-files',
				'title' => 'Excessive CSS Files',
				'description' => sprintf('%d CSS files detected. Each requires HTTP request. Consider combining/concatenating stylesheets.', $css_file_count),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/css-optimization/',
				'training_link' => 'https://wpshadow.com/training/asset-optimization/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['css_file_count' => $css_file_count],
			];
		}

		// 50KB+ of inline CSS is excessive
		if ($total_inline_css_size > 51200) {
			return [
				'id' => 'performance-excessive-inline-css',
				'title' => 'Excessive Inline CSS',
				'description' => sprintf(
					'%.1f KB of inline CSS detected. Large inline styles bloat HTML. Consider external stylesheets with caching.',
					$total_inline_css_size / 1024
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/inline-css/',
				'training_link' => 'https://wpshadow.com/training/asset-optimization/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['inline_css_size_kb' => round($total_inline_css_size / 1024, 1), 'inline_style_count' => $inline_style_count],
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
		return __('Excessive CSS', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for too many CSS files or excessive inline styles.', 'wpshadow');
	}
}

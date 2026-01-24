<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Critical_CSS extends Diagnostic_Base
{

	protected static $slug = 'test-performance-critical-css';
	protected static $title = 'Critical CSS Test';
	protected static $description = 'Tests for critical CSS inline implementation';

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
		// Check for inline critical CSS
		preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $inline_styles);
		$has_inline_css = !empty($inline_styles[0]);

		// Count external stylesheets in <head>
		if (preg_match('/<head[^>]*>(.*?)<\/head>/is', $html, $head_match)) {
			$head_content = $head_match[1];
			preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]+>/i', $head_content, $stylesheets);
			$stylesheet_count = count($stylesheets[0]);

			// Check for render-blocking CSS (without media="print" or preload)
			$blocking_css = 0;
			foreach ($stylesheets[0] as $stylesheet) {
				if (
					strpos($stylesheet, 'media="print"') === false &&
					strpos($stylesheet, 'rel="preload"') === false
				) {
					$blocking_css++;
				}
			}

			// If many external stylesheets but no inline critical CSS
			if ($blocking_css > 2 && !$has_inline_css) {
				return [
					'id' => 'performance-no-critical-css',
					'title' => 'No Critical CSS',
					'description' => sprintf('%d render-blocking stylesheets but no inline critical CSS. Critical CSS can improve First Contentful Paint by 200-500ms.', $blocking_css)
					'kb_link' => 'https://wpshadow.com/kb/critical-css/',
					'training_link' => 'https://wpshadow.com/training/css-optimization/',
					'auto_fixable' => false,
					'threat_level' => 30,
					'module' => 'Performance',
					'priority' => 3,
					'meta' => ['blocking_css' => $blocking_css, 'has_inline_css' => $has_inline_css],
				];
			}
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
		return __('Critical CSS', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for critical CSS inline implementation.', 'wpshadow');
	}
}

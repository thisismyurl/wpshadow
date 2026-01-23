<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Minification extends Diagnostic_Base
{

	protected static $slug = 'test-performance-minification';
	protected static $title = 'Asset Minification Test';
	protected static $description = 'Tests for minified CSS and JavaScript';

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
		preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]+href=["\']([^"\']+\.css)["\']/i', $html, $css_files);
		preg_match_all('/<link[^>]+href=["\']([^"\']+\.min\.css)["\']/i', $html, $minified_css);

		$total_css = count($css_files[0]);
		$minified_css_count = count($minified_css[0]);

		// Count JS files
		preg_match_all('/<script[^>]+src=["\']([^"\']+\.js)["\']/i', $html, $js_files);
		preg_match_all('/<script[^>]+src=["\']([^"\']+\.min\.js)["\']/i', $html, $minified_js);

		$total_js = count($js_files[0]);
		$minified_js_count = count($minified_js[0]);

		$unminified_css = $total_css - $minified_css_count;
		$unminified_js = $total_js - $minified_js_count;

		if (($unminified_css > 3 || $unminified_js > 3) && ($total_css > 0 || $total_js > 0)) {
			return [
				'id' => 'performance-unminified-assets',
				'title' => 'Unminified Assets Detected',
				'description' => sprintf('%d CSS and %d JS files are not minified. Minification can reduce file size by 20-40%%.', $unminified_css, $unminified_js),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/minification/',
				'training_link' => 'https://wpshadow.com/training/performance-optimization/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['unminified_css' => $unminified_css, 'unminified_js' => $unminified_js, 'total_css' => $total_css, 'total_js' => $total_js],
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
		return __('Asset Minification', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for minified CSS and JavaScript.', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Defer_JS extends Diagnostic_Base
{

	protected static $slug = 'test-performance-defer-js';
	protected static $title = 'Defer JavaScript Test';
	protected static $description = 'Tests for deferred JavaScript loading';

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
		// Count render-blocking JS in <head>
		if (preg_match('/<head[^>]*>(.*?)<\/head>/is', $html, $head_match)) {
			$head_content = $head_match[1];

			// Count script tags in head
			preg_match_all('/<script[^>]+src=/i', $head_content, $head_scripts);
			$total_head_scripts = count($head_scripts[0]);

			// Count deferred/async scripts
			preg_match_all('/<script[^>]+(defer|async)/i', $head_content, $deferred_scripts);
			$deferred_count = count($deferred_scripts[0]);

			$blocking_scripts = $total_head_scripts - $deferred_count;

			if ($blocking_scripts > 2) {
				return [
					'id' => 'performance-render-blocking-js',
					'title' => 'Render-Blocking JavaScript',
					'description' => sprintf('%d render-blocking JavaScript files in <head>. Use defer or async attributes to improve page load.', $blocking_scripts)
					'kb_link' => 'https://wpshadow.com/kb/defer-javascript/',
					'training_link' => 'https://wpshadow.com/training/javascript-optimization/',
					'auto_fixable' => false,
					'threat_level' => 45,
					'module' => 'Performance',
					'priority' => 2,
					'meta' => ['blocking_scripts' => $blocking_scripts, 'total_scripts' => $total_head_scripts],
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
		return __('Defer JavaScript', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for deferred JavaScript loading.', 'wpshadow');
	}
}

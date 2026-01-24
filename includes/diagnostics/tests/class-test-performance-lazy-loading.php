<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Lazy_Loading extends Diagnostic_Base
{

	protected static $slug = 'test-performance-lazy-loading';
	protected static $title = 'Lazy Loading Test';
	protected static $description = 'Tests for lazy loading on images';

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
		// Count total images
		preg_match_all('/<img[^>]+>/i', $html, $all_images);
		$total_images = count($all_images[0]);

		// Count images with loading="lazy"
		preg_match_all('/<img[^>]+loading=["\']lazy["\']/i', $html, $lazy_images);
		$lazy_count = count($lazy_images[0]);

		// Count images with data-src or similar lazy loading patterns
		preg_match_all('/<img[^>]+data-src=/i', $html, $js_lazy_images);
		$js_lazy_count = count($js_lazy_images[0]);

		$total_lazy = $lazy_count + $js_lazy_count;

		// If many images but no lazy loading
		if ($total_images > 5 && $total_lazy === 0) {
			return [
				'id' => 'performance-no-lazy-loading',
				'title' => 'No Lazy Loading',
				'description' => sprintf('%d images found without lazy loading. Lazy loading can reduce initial page load by 20-50%% on image-heavy pages.', $total_images)
				'kb_link' => 'https://wpshadow.com/kb/lazy-loading/',
				'training_link' => 'https://wpshadow.com/training/image-optimization/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['total_images' => $total_images, 'lazy_count' => $total_lazy],
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
		return __('Lazy Loading', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for lazy loading on images.', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Mobile_Friendly extends Diagnostic_Base
{

	protected static $slug = 'test-seo-mobile-friendly';
	protected static $title = 'Mobile-Friendly Tag Test';
	protected static $description = 'Tests for Google mobile-friendly indicators';

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
		// Check for viewport
		$has_viewport = preg_match('/<meta[^>]+name=["\']viewport["\']/i', $html);

		// Check for responsive meta tags
		$has_mobile_optimized = preg_match('/<meta[^>]+name=["\']MobileOptimized["\']/i', $html);
		$has_handheld_friendly = preg_match('/<meta[^>]+name=["\']HandheldFriendly["\']/i', $html);

		if (!$has_viewport) {
			return [
				'id' => 'seo-mobile-friendly-no-viewport',
				'title' => 'Missing Viewport Meta Tag',
				'description' => 'No viewport meta tag found. This is required for Google\'s mobile-friendly test and mobile-first indexing.',
				'color' => '#ff5722',
				'bg_color' => '#ffebee',
				'kb_link' => 'https://wpshadow.com/kb/viewport-meta-tag/',
				'training_link' => 'https://wpshadow.com/training/mobile-seo/',
				'auto_fixable' => false,
				'threat_level' => 60,
				'module' => 'SEO',
				'priority' => 1,
				'meta' => ['has_viewport' => false, 'checked_url' => $checked_url],
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
		return __('Mobile-Friendly Indicators', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for Google mobile-friendly indicators.', 'wpshadow');
	}
}

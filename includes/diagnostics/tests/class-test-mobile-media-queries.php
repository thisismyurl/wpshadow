<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Media_Queries extends Diagnostic_Base
{

	protected static $slug = 'test-mobile-media-queries';
	protected static $title = 'Mobile Media Queries Test';
	protected static $description = 'Tests for responsive CSS media queries';

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
		// Check for media queries in inline styles and style tags
		preg_match_all('/@media[^{]+\{/i', $html, $media_queries);
		$media_query_count = count($media_queries[0]);

		// Check for mobile-specific breakpoints (common patterns)
		$has_mobile_breakpoint = preg_match('/@media[^{]*(max-width:\s*[0-9]+px|min-width:\s*[0-9]+px)/i', $html);

		// Check linked stylesheets (harder to analyze, just check presence)
		preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]+>/i', $html, $stylesheets);
		$stylesheet_count = count($stylesheets[0]);

		// If no media queries and stylesheets exist, might not be responsive
		if ($media_query_count === 0 && !$has_mobile_breakpoint && $stylesheet_count < 2) {
			return [
				'id' => 'mobile-media-queries-missing',
				'title' => 'No Mobile Media Queries Detected',
				'description' => 'No CSS media queries detected in inline styles. Mobile-responsive design typically uses @media queries for different screen sizes.'
				'kb_link' => 'https://wpshadow.com/kb/media-queries/',
				'training_link' => 'https://wpshadow.com/training/responsive-css/',
				'auto_fixable' => false,
				'threat_level' => 50,
				'module' => 'Mobile',
				'priority' => 2,
				'meta' => [
					'media_query_count' => $media_query_count,
					'has_mobile_breakpoint' => $has_mobile_breakpoint,
					'stylesheet_count' => $stylesheet_count,
					'checked_url' => $checked_url,
				],
			];
		}

		return null; // PASS - has media queries
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Mobile Media Queries', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for responsive CSS media queries.', 'wpshadow');
	}
}

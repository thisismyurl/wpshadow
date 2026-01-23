<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Call_To_Action extends Diagnostic_Base
{

	protected static $slug = 'test-content-call-to-action';
	protected static $title = 'Call-to-Action Test';
	protected static $description = 'Tests for clear call-to-action on content';

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
		// Check if it's a landing page or blog post
		$is_content_page = (preg_match('/<article[^>]*>/i', $html) ||
			preg_match('/class=["\'][^"\']*(?:post|entry|page)[^"\']*["\']/i', $html));

		if (!$is_content_page) {
			return null;
		}

		// Look for CTA patterns
		$cta_patterns = [
			'/class=["\'][^"\']*(?:cta|call[_-]?to[_-]?action|button[_-]?primary)[^"\']*["\']/i',
			'/<button[^>]+class=["\'][^"\']*(?:primary|cta|subscribe|signup)[^"\']*["\']/i',
			'/<a[^>]+class=["\'][^"\']*(?:btn|button)[^"\']*["\']/i',
			'/(?:subscribe|sign\s*up|get\s+started|learn\s+more|download|buy\s+now|contact\s+us)/i'
		];

		$cta_count = 0;
		foreach ($cta_patterns as $pattern) {
			$cta_count += preg_match_all($pattern, $html, $matches);
		}

		if ($cta_count === 0) {
			return [
				'id' => 'content-no-cta',
				'title' => 'No Clear Call-to-Action',
				'description' => 'Content page without clear call-to-action. CTAs guide users toward conversion goals and improve engagement.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/call-to-action/',
				'training_link' => 'https://wpshadow.com/training/conversion-optimization/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['cta_count' => 0],
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
		return __('Call-to-Action', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for clear call-to-action on content.', 'wpshadow');
	}
}

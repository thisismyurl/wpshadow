<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_Skip_Links extends Diagnostic_Base
{

	protected static $slug = 'test-accessibility-skip-links';
	protected static $title = 'Skip Links Test';
	protected static $description = 'Tests for skip navigation links';

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
		// Look for skip links (usually at the beginning of <body>)
		$has_skip_link = preg_match('/<a[^>]+href=["\']#[^"\']*(?:content|main|skip)[^"\']*["\'][^>]*>(?:skip|jump)/i', $html);

		if (!$has_skip_link) {
			return [
				'id' => 'accessibility-no-skip-links',
				'title' => 'No Skip Navigation Links',
				'description' => 'No skip links detected. Skip links allow keyboard users to bypass repetitive navigation, critical for accessibility.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/skip-links/',
				'training_link' => 'https://wpshadow.com/training/keyboard-accessibility/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'Accessibility',
				'priority' => 2,
				'meta' => ['has_skip_link' => false],
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
		return __('Skip Links', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for skip navigation links.', 'wpshadow');
	}
}

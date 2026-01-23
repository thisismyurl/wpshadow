<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Debug_Mode extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-debug-mode';
	protected static $title = 'Debug Mode Test';
	protected static $description = 'Tests for WordPress debug mode on production';

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
		// Look for debug output in HTML
		$has_debug_output = preg_match('/Fatal error:|Warning:|Notice:|Parse error:|Deprecated:/i', $html);

		// Check for query monitor or similar debug tools visible
		$has_debug_tools = preg_match('/query-monitor|debug-bar/i', $html);

		if ($has_debug_output) {
			return [
				'id' => 'wordpress-debug-visible',
				'title' => 'Debug Output Visible',
				'description' => 'PHP errors/warnings visible in page HTML. Debug mode should be disabled on production sites. Exposes system information to attackers.',
				'color' => '#ff5722',
				'bg_color' => '#ffebee',
				'kb_link' => 'https://wpshadow.com/kb/debug-mode/',
				'training_link' => 'https://wpshadow.com/training/wordpress-configuration/',
				'auto_fixable' => false,
				'threat_level' => 60,
				'module' => 'WordPress',
				'priority' => 1,
				'meta' => ['has_debug_output' => true],
			];
		}

		if ($has_debug_tools) {
			return [
				'id' => 'wordpress-debug-tools-visible',
				'title' => 'Debug Tools Visible',
				'description' => 'Debug tools (Query Monitor, Debug Bar) visible on production. These should only be used in development environments.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/debug-tools/',
				'training_link' => 'https://wpshadow.com/training/wordpress-configuration/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'WordPress',
				'priority' => 2,
				'meta' => ['has_debug_tools' => true],
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
		return __('Debug Mode', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for WordPress debug mode on production.', 'wpshadow');
	}
}

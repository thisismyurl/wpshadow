<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Navigation extends Diagnostic_Base
{

	protected static $slug = 'test-mobile-navigation';
	protected static $title = 'Mobile Navigation Test';
	protected static $description = 'Tests for mobile-friendly navigation patterns';

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
		// Check for hamburger menu or mobile nav indicators
		$has_mobile_nav = preg_match('/menu-toggle|hamburger|mobile-menu|nav-toggle/i', $html);

		// Count navigation items
		preg_match_all('/<nav[^>]*>.*?<\/nav>/is', $html, $nav_elements);

		if (empty($nav_elements[0])) {
			return null; // No navigation
		}

		// Count menu items in first nav
		$nav_html = $nav_elements[0][0] ?? '';
		preg_match_all('/<a[^>]*>/i', $nav_html, $nav_links);
		$link_count = count($nav_links[0]);

		// If many links but no mobile menu pattern, flag it
		if ($link_count > 7 && !$has_mobile_nav) {
			return [
				'id' => 'mobile-navigation',
				'title' => 'No Mobile Navigation Pattern Detected',
				'description' => sprintf(
					'Navigation has %d links but no mobile menu pattern detected. Consider using a hamburger menu or collapsible navigation for mobile.',
					$link_count
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/mobile-navigation/',
				'training_link' => 'https://wpshadow.com/training/mobile-ux/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'Mobile',
				'priority' => 2,
				'meta' => [
					'nav_link_count' => $link_count,
					'has_mobile_pattern' => $has_mobile_nav,
					'checked_url' => $checked_url,
				],
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
		return __('Mobile Navigation', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for mobile-friendly navigation (hamburger menu, etc).', 'wpshadow');
	}
}

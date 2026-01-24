<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Focus_Indicators extends Diagnostic_Base
{

	protected static $slug = 'test-ux-focus-indicators';
	protected static $title = 'Focus Indicators Test';
	protected static $description = 'Tests for visible keyboard focus indicators';

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
		// Check if outline is removed (bad practice)
		$removes_outline = preg_match('/outline:\s*none|outline:\s*0/i', $html);

		// Check for custom focus styles (good)
		$has_focus_styles = preg_match('/:focus[^{]*\{[^}]*(border|box-shadow|background|outline:[^n])/i', $html);

		// If outline removed but no custom focus styles
		if ($removes_outline && !$has_focus_styles) {
			return [
				'id' => 'ux-focus-indicators-removed',
				'title' => 'Focus Indicators Removed',
				'description' => 'CSS removes focus outlines (outline:none) without custom focus styles. Keyboard users need visible focus indicators (WCAG 2.4.7).'
				'kb_link' => 'https://wpshadow.com/kb/focus-indicators/',
				'training_link' => 'https://wpshadow.com/training/keyboard-accessibility/',
				'auto_fixable' => false,
				'threat_level' => 60,
				'module' => 'Accessibility',
				'priority' => 1,
				'meta' => [
					'removes_outline' => $removes_outline,
					'has_custom_focus' => $has_focus_styles,
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
		return __('Focus Indicators', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for visible keyboard focus indicators (WCAG 2.4.7).', 'wpshadow');
	}
}

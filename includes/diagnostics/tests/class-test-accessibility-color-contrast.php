<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_Color_Contrast extends Diagnostic_Base
{

	protected static $slug = 'test-accessibility-color-contrast';
	protected static $title = 'Color Contrast Test';
	protected static $description = 'Basic color contrast detection';

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
		// Look for obvious low-contrast patterns in inline styles
		$low_contrast_patterns = [
			'/color:\s*#[cdef][cdef][cdef][cdef][cdef][cdef]/i', // Very light colors
			'/background:\s*#[0123][0123][0123][0123][0123][0123].*color:\s*#[0123][0123][0123][0123][0123][0123]/i', // Dark on dark
			'/background:\s*#[ef][ef][ef][ef][ef][ef].*color:\s*#[ef][ef][ef][ef][ef][ef]/i', // Light on light
		];

		$potential_issues = 0;
		foreach ($low_contrast_patterns as $pattern) {
			preg_match_all($pattern, $html, $matches);
			$potential_issues += count($matches[0]);
		}

		// Also check for common problematic combos
		if (preg_match('/color:\s*#gray|color:\s*gray\b/i', $html)) {
			$potential_issues++;
		}

		if ($potential_issues === 0) {
			return null; // No obvious issues
		}

		return [
			'id' => 'accessibility-color-contrast',
			'title' => 'Potential Color Contrast Issues',
			'description' => sprintf(
				'Detected %d potential color contrast issues. Low contrast makes text hard to read and violates WCAG accessibility guidelines. Text should have a 4.5:1 contrast ratio.',
				$potential_issues
			)
			'kb_link' => 'https://wpshadow.com/kb/color-contrast/',
			'training_link' => 'https://wpshadow.com/training/accessibility-design/',
			'auto_fixable' => false,
			'threat_level' => 40,
			'module' => 'Accessibility',
			'priority' => 2,
			'meta' => [
				'potential_issues' => $potential_issues,
				'checked_url' => $checked_url,
			],
		];
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Color Contrast Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Basic detection of potential color contrast issues.', 'wpshadow');
	}
}

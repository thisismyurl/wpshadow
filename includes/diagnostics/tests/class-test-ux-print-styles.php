<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Print_Styles extends Diagnostic_Base
{

	protected static $slug = 'test-ux-print-styles';
	protected static $title = 'Print Styles Test';
	protected static $description = 'Tests for print-optimized CSS';

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
		// Check for print media query
		$has_print_media = preg_match('/@media\s+print|media=["\']print["\']/i', $html);

		// Check for print stylesheet
		$has_print_css = preg_match('/<link[^>]*media=["\']print["\'][^>]*>/i', $html);

		// If no print styles found
		if (!$has_print_media && !$has_print_css) {
			return [
				'id' => 'ux-print-styles-missing',
				'title' => 'Print Styles Missing',
				'description' => 'No print media queries or print stylesheets detected. Print styles remove navigation, optimize layout, and improve readability when printing.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/print-styles/',
				'training_link' => 'https://wpshadow.com/training/css-best-practices/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'UX',
				'priority' => 4,
				'meta' => [
					'has_print_media' => $has_print_media,
					'has_print_css' => $has_print_css,
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
		return __('Print Styles', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for print-optimized CSS (@media print).', 'wpshadow');
	}
}

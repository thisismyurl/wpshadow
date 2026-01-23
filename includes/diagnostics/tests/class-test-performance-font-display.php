<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Font_Display extends Diagnostic_Base
{

	protected static $slug = 'test-performance-font-display';
	protected static $title = 'Font Display Strategy Test';
	protected static $description = 'Tests for font-display CSS property';

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
		// Check for @font-face declarations
		preg_match_all('/@font-face\s*{([^}]+)}/is', $html, $font_faces);

		if (empty($font_faces[0])) {
			return null; // No custom fonts, test not applicable
		}

		// Check if font-display is used
		$has_font_display = false;
		foreach ($font_faces[1] as $font_face_content) {
			if (preg_match('/font-display\s*:\s*(swap|fallback|optional)/i', $font_face_content)) {
				$has_font_display = true;
				break;
			}
		}

		// Check for Google Fonts with display parameter
		$has_google_fonts = preg_match('/fonts\.googleapis\.com/i', $html);
		$has_display_param = preg_match('/fonts\.googleapis\.com[^"\']*[?&]display=(swap|fallback|optional)/i', $html);

		if (count($font_faces[0]) > 0 && !$has_font_display) {
			return [
				'id' => 'performance-no-font-display',
				'title' => 'Missing Font Display Strategy',
				'description' => sprintf('%d @font-face declarations without font-display property. Can cause FOIT (Flash of Invisible Text) for 3+ seconds.', count($font_faces[0])),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/font-display/',
				'training_link' => 'https://wpshadow.com/training/font-optimization/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['font_face_count' => count($font_faces[0])],
			];
		}

		if ($has_google_fonts && !$has_display_param) {
			return [
				'id' => 'performance-google-fonts-no-display',
				'title' => 'Google Fonts Missing Display Parameter',
				'description' => 'Google Fonts loaded without &display=swap parameter. Can cause text to be invisible during font load.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/google-fonts-optimization/',
				'training_link' => 'https://wpshadow.com/training/font-optimization/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['has_google_fonts' => true],
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
		return __('Font Display Strategy', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for font-display CSS property.', 'wpshadow');
	}
}

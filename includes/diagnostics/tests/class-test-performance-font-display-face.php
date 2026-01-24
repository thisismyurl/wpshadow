<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Font_Display_Face extends Diagnostic_Base
{

	protected static $slug = 'test-performance-font-display-face';
	protected static $title = 'Font Display (Custom Fonts) Test';
	protected static $description = 'Tests @font-face declarations for missing font-display.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$body = $html ?? self::fetch_html($url ?? home_url('/'));
		if ($body === false) {
			return null;
		}

		preg_match_all('/@font-face\s*{([^}]+)}/is', $body, $font_faces);

		if (empty($font_faces[0])) {
			return null; // No custom fonts
		}

		$has_font_display = false;
		foreach ($font_faces[1] as $font_face_content) {
			if (preg_match('/font-display\s*:\s*(swap|fallback|optional)/i', $font_face_content)) {
				$has_font_display = true;
				break;
			}
		}

		if (!$has_font_display) {
			return [
				'id' => 'performance-no-font-display',
				'title' => 'Missing Font Display Strategy',
				'description' => sprintf('%d @font-face declarations without font-display property. Can cause FOIT (Flash of Invisible Text) for 3+ seconds.', count($font_faces[0]))
				'kb_link' => 'https://wpshadow.com/kb/font-display/',
				'training_link' => 'https://wpshadow.com/training/font-optimization/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['font_face_count' => count($font_faces[0])],
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
		return __('Font Display (Custom Fonts)', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks @font-face rules for the font-display property.', 'wpshadow');
	}
}

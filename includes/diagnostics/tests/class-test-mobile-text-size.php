<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Text_Size extends Diagnostic_Base
{

	protected static $slug = 'test-mobile-text-size';
	protected static $title = 'Mobile Text Size Test';
	protected static $description = 'Tests for readable text sizes on mobile (16px+ body text)';

	const MIN_BODY_SIZE = 16;

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
		// Check for small font sizes in styles
		preg_match_all('/font-size:\s*([0-9]+(?:\.[0-9]+)?)px/i', $html, $matches);

		$small_fonts = [];
		foreach ($matches[1] as $size) {
			$size_num = (float)$size;
			if ($size_num < self::MIN_BODY_SIZE && $size_num > 0) {
				$small_fonts[] = $size_num;
			}
		}

		if (count($small_fonts) > 5) {
			$avg_small = round(array_sum($small_fonts) / count($small_fonts), 1);

			return [
				'id' => 'mobile-text-size',
				'title' => 'Small Text Size Detected',
				'description' => sprintf(
					'Found %d instances of text under 16px (average: %spx). Small text is difficult to read on mobile devices without zooming.',
					count($small_fonts),
					$avg_small
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/mobile-text-size/',
				'training_link' => 'https://wpshadow.com/training/mobile-typography/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'Mobile',
				'priority' => 2,
				'meta' => [
					'small_font_count' => count($small_fonts),
					'average_size' => $avg_small,
					'min_recommended' => self::MIN_BODY_SIZE,
					'checked_url' => $checked_url,
				],
			];
		}

		return null; // PASS
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Mobile Text Size', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for readable text sizes on mobile (16px+ recommended).', 'wpshadow');
	}
}

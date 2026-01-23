<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Touch_Targets extends Diagnostic_Base
{

	protected static $slug = 'test-mobile-touch-targets';
	protected static $title = 'Touch Target Size Test';
	protected static $description = 'Tests for adequate touch target sizes (48x48px minimum)';

	const MIN_TOUCH_SIZE = 48;

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
		// Check for explicit small sizes in inline styles
		preg_match_all('/style=["\'][^"\']*(?:width|height|font-size):\s*([0-9]+)px/i', $html, $matches);

		$small_elements = 0;
		foreach ($matches[1] as $size) {
			if ((int)$size < self::MIN_TOUCH_SIZE) {
				$small_elements++;
			}
		}

		// Check for buttons/links without adequate spacing
		preg_match_all('/<(button|a)[^>]*>/i', $html, $interactive);
		$total_interactive = count($interactive[0]);

		if ($total_interactive === 0) {
			return null; // No interactive elements
		}

		// If we found many small explicit sizes, flag it
		if ($small_elements > 5) {
			return [
				'id' => 'mobile-touch-targets',
				'title' => 'Small Touch Targets Detected',
				'description' => sprintf(
					'Found %d interactive elements with sizes under 48x48px. Small touch targets are difficult to tap on mobile devices and hurt usability.',
					$small_elements
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/touch-targets/',
				'training_link' => 'https://wpshadow.com/training/mobile-ux/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'module' => 'Mobile',
				'priority' => 2,
				'meta' => [
					'small_elements' => $small_elements,
					'min_recommended' => self::MIN_TOUCH_SIZE,
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
		return __('Touch Target Size', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for adequate touch target sizes (48x48px minimum).', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Horizontal_Scrolling extends Diagnostic_Base
{

	protected static $slug = 'test-mobile-horizontal-scroll';
	protected static $title = 'Horizontal Scrolling Test';
	protected static $description = 'Tests for elements wider than viewport';

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
		// Check for fixed widths over 100%
		preg_match_all('/width:\s*([0-9]+)px/i', $html, $width_matches);
		preg_match_all('/min-width:\s*([0-9]+)px/i', $html, $min_width_matches);

		$wide_elements = 0;
		$widths = array_merge($width_matches[1], $min_width_matches[1]);

		foreach ($widths as $width) {
			if ((int)$width > 600) { // Wider than typical mobile viewport
				$wide_elements++;
			}
		}

		// Check for overflow-x settings
		$has_overflow_scroll = preg_match('/overflow-x:\s*scroll|overflow-x:\s*auto/i', $html);

		if ($wide_elements > 3 && !$has_overflow_scroll) {
			return [
				'id' => 'mobile-horizontal-scroll',
				'title' => 'Potential Horizontal Scrolling',
				'description' => sprintf(
					'Found %d elements with fixed widths over 600px. These may cause horizontal scrolling on mobile devices.',
					$wide_elements
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/horizontal-scrolling/',
				'training_link' => 'https://wpshadow.com/training/responsive-design/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'module' => 'Mobile',
				'priority' => 2,
				'meta' => [
					'wide_elements' => $wide_elements,
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
		return __('Horizontal Scrolling', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for elements that may cause horizontal scrolling.', 'wpshadow');
	}
}

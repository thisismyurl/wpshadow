<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_Landmarks extends Diagnostic_Base
{

	protected static $slug = 'test-accessibility-landmarks';
	protected static $title = 'ARIA Landmarks Test';
	protected static $description = 'Tests for proper ARIA landmark usage';

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
		// Check for semantic HTML5 elements or ARIA landmarks
		$has_main = preg_match('/<main[^>]*>|role=["\']main["\']/i', $html);
		$has_nav = preg_match('/<nav[^>]*>|role=["\']navigation["\']/i', $html);
		$has_header = preg_match('/<header[^>]*>|role=["\']banner["\']/i', $html);
		$has_footer = preg_match('/<footer[^>]*>|role=["\']contentinfo["\']/i', $html);

		$missing = [];
		if (!$has_main) $missing[] = 'main';
		if (!$has_nav) $missing[] = 'navigation';
		if (!$has_header) $missing[] = 'header/banner';
		if (!$has_footer) $missing[] = 'footer/contentinfo';

		if (count($missing) >= 2) {
			return [
				'id' => 'accessibility-missing-landmarks',
				'title' => 'Missing ARIA Landmarks',
				'description' => sprintf('Missing landmarks: %s. Landmarks help screen reader users navigate page sections.', implode(', ', $missing)),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/aria-landmarks/',
				'training_link' => 'https://wpshadow.com/training/semantic-html/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Accessibility',
				'priority' => 2,
				'meta' => ['missing_landmarks' => $missing],
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
		return __('ARIA Landmarks', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for proper ARIA landmark usage.', 'wpshadow');
	}
}

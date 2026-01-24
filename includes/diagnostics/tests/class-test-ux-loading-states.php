<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Loading_States extends Diagnostic_Base
{

	protected static $slug = 'test-ux-loading-states';
	protected static $title = 'Loading States Test';
	protected static $description = 'Tests for loading indicators (spinners, skeletons)';

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
		// Check for AJAX indicators
		$has_ajax = preg_match('/ajax|fetch\(|XMLHttpRequest/i', $html);

		// Check for loading indicators
		$has_loader = preg_match('/spinner|loader|loading|skeleton|class=["\'][^"\']*loading/i', $html);

		// Check for aria-busy
		$has_aria_busy = preg_match('/aria-busy=/i', $html);

		// If AJAX present but no loading indicators
		if ($has_ajax && !$has_loader && !$has_aria_busy) {
			return [
				'id' => 'ux-loading-states-missing',
				'title' => 'No Loading Indicators Found',
				'description' => 'AJAX/fetch calls detected but no loading indicators (spinners, skeleton screens, aria-busy). Users need feedback during async operations.'
				'kb_link' => 'https://wpshadow.com/kb/loading-states/',
				'training_link' => 'https://wpshadow.com/training/async-ux/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'UX',
				'priority' => 3,
				'meta' => [
					'has_ajax' => $has_ajax,
					'has_loader' => $has_loader,
					'has_aria_busy' => $has_aria_busy,
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
		return __('Loading States', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for loading indicators (spinners, skeletons, aria-busy).', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Progress_Indicators extends Diagnostic_Base
{

	protected static $slug = 'test-ux-progress-indicators';
	protected static $title = 'Progress Indicators Test';
	protected static $description = 'Tests for progress bars in multi-step processes';

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
		// Check for multi-step indicators
		$has_steps = preg_match('/step\s*[0-9]+|page\s*[0-9]+\s*of\s*[0-9]+/i', $html);

		// Check for progress bar
		$has_progress_bar = preg_match('/<progress[^>]*>|role=["\']progressbar|class=["\'][^"\']*progress/i', $html);

		// Check for stepper/wizard
		$has_stepper = preg_match('/class=["\'][^"\']*stepper|class=["\'][^"\']*wizard|aria-current=["\']step/i', $html);

		// If multi-step but no progress indicator
		if ($has_steps && !$has_progress_bar && !$has_stepper) {
			return [
				'id' => 'ux-progress-indicators-missing',
				'title' => 'Multi-Step Process Missing Progress Indicator',
				'description' => 'Multi-step process detected (step numbers) but no visual progress indicator. Use progress bars or stepper components to show completion status.'
				'kb_link' => 'https://wpshadow.com/kb/progress-indicators/',
				'training_link' => 'https://wpshadow.com/training/multi-step-forms/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'UX',
				'priority' => 3,
				'meta' => [
					'has_steps' => $has_steps,
					'has_progress_bar' => $has_progress_bar,
					'has_stepper' => $has_stepper,
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
		return __('Progress Indicators', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for progress indicators in multi-step processes.', 'wpshadow');
	}
}

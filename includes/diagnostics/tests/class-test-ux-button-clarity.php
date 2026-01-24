<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Button_Clarity extends Diagnostic_Base
{

	protected static $slug = 'test-ux-button-clarity';
	protected static $title = 'Button Text Clarity Test';
	protected static $description = 'Tests for clear, action-oriented button text';

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
		// Find all buttons
		preg_match_all('/<button[^>]*>(.*?)<\/button>/is', $html, $button_matches);
		preg_match_all('/<input[^>]*type=["\']submit["\'][^>]*(value=["\']([^"\']+)["\'])?/i', $html, $submit_matches);

		$all_buttons = array_merge($button_matches[1], $submit_matches[2] ?? []);

		if (empty($all_buttons)) {
			return null;
		}

		// Vague button text patterns
		$vague_patterns = [
			'submit',
			'ok',
			'yes',
			'no',
			'go',
			'send',
			'button',
		];

		$vague_buttons = 0;
		$examples = [];

		foreach ($all_buttons as $button_text) {
			$clean_text = strtolower(strip_tags(trim($button_text)));

			if (empty($clean_text)) {
				$vague_buttons++; // Empty button
				continue;
			}

			// Single-word buttons are often vague
			if (str_word_count($clean_text) === 1 && in_array($clean_text, $vague_patterns, true)) {
				$vague_buttons++;
				if (count($examples) < 3) {
					$examples[] = $clean_text;
				}
			}
		}

		if ($vague_buttons > 2) {
			return [
				'id' => 'ux-button-clarity',
				'title' => 'Vague Button Text',
				'description' => sprintf(
					'Found %d buttons with vague text (%s). Use action-oriented text like "Download PDF" or "Subscribe to Newsletter".',
					$vague_buttons,
					implode(', ', array_slice($examples, 0, 3))
				)
				'kb_link' => 'https://wpshadow.com/kb/button-best-practices/',
				'training_link' => 'https://wpshadow.com/training/conversion-ux/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'UX',
				'priority' => 3,
				'meta' => [
					'vague_buttons' => $vague_buttons,
					'examples' => $examples,
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
		return __('Button Text Clarity', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for clear, action-oriented button text.', 'wpshadow');
	}
}

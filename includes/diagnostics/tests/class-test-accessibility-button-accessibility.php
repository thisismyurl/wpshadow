<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_Button_Accessibility extends Diagnostic_Base
{

	protected static $slug = 'test-accessibility-button-accessibility';
	protected static $title = 'Button Accessibility Test';
	protected static $description = 'Tests for proper button implementation';

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
		// Find divs/spans being used as buttons (with onClick)
		preg_match_all('/<(?:div|span)[^>]+onclick=/i', $html, $fake_buttons);
		$fake_button_count = count($fake_buttons[0]);

		// Find links styled as buttons with # href
		preg_match_all('/<a[^>]+href=["\']#["\'][^>]*class=["\'][^"\']*(?:btn|button)[^"\']*["\']/i', $html, $link_buttons);
		$link_button_count = count($link_buttons[0]);

		if ($fake_button_count > 2) {
			return [
				'id' => 'accessibility-fake-buttons',
				'title' => 'Improper Button Implementation',
				'description' => sprintf('%d div/span elements used as buttons (onclick). Use proper <button> elements for accessibility and keyboard support.', $fake_button_count)
				'kb_link' => 'https://wpshadow.com/kb/button-accessibility/',
				'training_link' => 'https://wpshadow.com/training/semantic-html/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'Accessibility',
				'priority' => 2,
				'meta' => ['fake_buttons' => $fake_button_count],
			];
		}

		if ($link_button_count > 3) {
			return [
				'id' => 'accessibility-links-as-buttons',
				'title' => 'Links Used as Buttons',
				'description' => sprintf('%d links with href="#" styled as buttons. If it performs an action (not navigation), use <button> instead.', $link_button_count)
				'kb_link' => 'https://wpshadow.com/kb/buttons-vs-links/',
				'training_link' => 'https://wpshadow.com/training/semantic-html/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'Accessibility',
				'priority' => 3,
				'meta' => ['link_buttons' => $link_button_count],
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
		return __('Button Accessibility', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for proper button implementation.', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Keyboard_Navigation extends Diagnostic_Base
{

	protected static $slug = 'test-ux-keyboard-navigation';
	protected static $title = 'Keyboard Navigation Test';
	protected static $description = 'Tests for keyboard-accessible interactive elements';

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
		// Check for click handlers on non-interactive elements
		preg_match_all('/<div[^>]*onclick|<span[^>]*onclick/i', $html, $div_clicks);
		$non_interactive_clicks = count($div_clicks[0]);

		// Check if these have tabindex
		$has_tabindex = preg_match_all('/tabindex=["\'][0-9]+["\']/i', $html, $tabindex_matches);
		$tabindex_count = count($tabindex_matches[0]);

		// Check for role=button on non-buttons
		$has_role_button = preg_match_all('/role=["\']button["\']/i', $html, $role_matches);
		$role_button_count = count($role_matches[0]);

		// If click handlers on divs/spans without proper accessibility
		if ($non_interactive_clicks > 2 && ($tabindex_count < $non_interactive_clicks / 2)) {
			return [
				'id' => 'ux-keyboard-navigation-issues',
				'title' => 'Non-Keyboard-Accessible Click Handlers',
				'description' => sprintf(
					'Found %d click handlers on non-interactive elements (div, span) with insufficient tabindex attributes. Use <button> or add tabindex="0" and keyboard event handlers.',
					$non_interactive_clicks
				),
				'color' => '#ff5722',
				'bg_color' => '#ffebee',
				'kb_link' => 'https://wpshadow.com/kb/keyboard-navigation/',
				'training_link' => 'https://wpshadow.com/training/accessible-interactions/',
				'auto_fixable' => false,
				'threat_level' => 55,
				'module' => 'Accessibility',
				'priority' => 1,
				'meta' => [
					'non_interactive_clicks' => $non_interactive_clicks,
					'tabindex_count' => $tabindex_count,
					'role_button_count' => $role_button_count,
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
		return __('Keyboard Navigation', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for keyboard-accessible interactive elements.', 'wpshadow');
	}
}

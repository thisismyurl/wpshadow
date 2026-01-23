<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Forms extends Diagnostic_Base
{

	protected static $slug = 'test-mobile-forms';
	protected static $title = 'Mobile Forms Test';
	protected static $description = 'Tests for mobile-friendly form inputs';

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
		// Count form inputs
		preg_match_all('/<input[^>]*type=["\']?(text|email|tel|number|search)["\']?[^>]*>/i', $html, $inputs);
		$input_count = count($inputs[0]);

		if ($input_count === 0) {
			return null; // No forms to check
		}

		// Check for appropriate input types (email, tel, etc)
		$has_email_type = preg_match('/<input[^>]*type=["\']email["\']/i', $html);
		$has_tel_type = preg_match('/<input[^>]*type=["\']tel["\']/i', $html);
		$has_number_type = preg_match('/<input[^>]*type=["\']number["\']/i', $html);

		// Check for autocomplete attributes
		preg_match_all('/<input[^>]*autocomplete=/i', $html, $autocomplete_inputs);
		$autocomplete_count = count($autocomplete_inputs[0]);

		// Percentage with autocomplete
		$autocomplete_percentage = $input_count > 0 ? ($autocomplete_count / $input_count) * 100 : 0;

		// Flag if forms exist but no mobile-friendly input types or autocomplete
		$mobile_friendly_types = $has_email_type || $has_tel_type || $has_number_type;

		if (!$mobile_friendly_types && $autocomplete_percentage < 30) {
			return [
				'id' => 'mobile-forms-not-optimized',
				'title' => 'Forms Not Mobile-Optimized',
				'description' => sprintf(
					'Found %d form inputs but no mobile-specific input types (email, tel) or autocomplete attributes. Mobile users benefit from correct input types and autocomplete.',
					$input_count
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/mobile-forms/',
				'training_link' => 'https://wpshadow.com/training/mobile-ux/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'Mobile',
				'priority' => 2,
				'meta' => [
					'input_count' => $input_count,
					'autocomplete_percentage' => round($autocomplete_percentage, 1),
					'has_mobile_types' => $mobile_friendly_types,
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
		return __('Mobile Forms', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for mobile-friendly form inputs (type, autocomplete).', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Software extends Diagnostic_Base
{

	protected static $slug = 'test-schema-software';
	protected static $title = 'SoftwareApplication Schema Test';
	protected static $description = 'Tests for SoftwareApplication structured data';

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
		// Check for software/app indicators
		$has_software_keywords = preg_match('/\b(download|install|app|software|application|version|platform|system requirements)\b/i', $html);
		$has_download_button = preg_match('/<(button|a)[^>]*(download|install)/i', $html);
		$has_os_mentions = preg_match('/\b(Windows|Mac|Linux|iOS|Android|operating system)\b/i', $html);

		// Check for SoftwareApplication schema
		$has_software_schema = preg_match('/"@type"\s*:\s*"SoftwareApplication"/i', $html);

		// If looks like software page but no schema
		if ($has_software_keywords && ($has_download_button || $has_os_mentions) && !$has_software_schema) {
			return [
				'id' => 'schema-software-missing',
				'title' => 'SoftwareApplication Schema Missing',
				'description' => 'Software/app content detected but no SoftwareApplication structured data found. This schema enables rich results with ratings, version, and platform info.'
				'kb_link' => 'https://wpshadow.com/kb/software-schema/',
				'training_link' => 'https://wpshadow.com/training/structured-data/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => [
					'has_software_keywords' => $has_software_keywords,
					'has_download_button' => $has_download_button,
					'has_os_mentions' => $has_os_mentions,
					'has_schema' => $has_software_schema,
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
		return __('SoftwareApplication Schema', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for SoftwareApplication structured data.', 'wpshadow');
	}
}

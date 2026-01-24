<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Breadcrumb extends Diagnostic_Base
{

	protected static $slug = 'test-schema-breadcrumb';
	protected static $title = 'BreadcrumbList Schema Test';
	protected static $description = 'Tests for BreadcrumbList structured data';

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
		// Check for BreadcrumbList schema
		$has_breadcrumb_schema = preg_match('/"@type"\s*:\s*"BreadcrumbList"/i', $html);

		// Check for breadcrumb HTML
		$has_breadcrumb_html = preg_match('/<nav[^>]*aria-label=["\']breadcrumb|class=["\'][^"\']*breadcrumb/i', $html);

		if ($has_breadcrumb_html && !$has_breadcrumb_schema) {
			return [
				'id' => 'schema-breadcrumb-missing',
				'title' => 'BreadcrumbList Schema Missing',
				'description' => 'Breadcrumb navigation detected but no BreadcrumbList structured data found. Adding schema helps search engines understand site hierarchy.'
				'kb_link' => 'https://wpshadow.com/kb/breadcrumb-schema/',
				'training_link' => 'https://wpshadow.com/training/structured-data/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => [
					'has_breadcrumb_html' => $has_breadcrumb_html,
					'has_schema' => $has_breadcrumb_schema,
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
		return __('BreadcrumbList Schema', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for BreadcrumbList structured data.', 'wpshadow');
	}
}

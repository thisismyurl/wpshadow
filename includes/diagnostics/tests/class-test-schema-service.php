<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Service extends Diagnostic_Base
{

	protected static $slug = 'test-schema-service';
	protected static $title = 'Service Schema Test';
	protected static $description = 'Tests for Service structured data';

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
		// Check for service indicators
		$has_service_keywords = preg_match('/\b(service|consultation|booking|appointment|pricing|packages|plans)\b/i', $html);
		$has_cta = preg_match('/<(button|a)[^>]*(book|schedule|contact|get quote)/i', $html);
		$has_pricing = preg_match('/\$[0-9,]+|pricing|cost|fee|rate/i', $html);

		// Check for Service schema
		$has_service_schema = preg_match('/"@type"\s*:\s*"Service"/i', $html);

		// If looks like service page but no schema
		if ($has_service_keywords && ($has_cta || $has_pricing) && !$has_service_schema) {
			return [
				'id' => 'schema-service-missing',
				'title' => 'Service Schema Missing',
				'description' => 'Service offering detected but no Service structured data found. Service schema helps search engines understand your offerings and can appear in local search.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/service-schema/',
				'training_link' => 'https://wpshadow.com/training/local-seo/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => [
					'has_service_keywords' => $has_service_keywords,
					'has_cta' => $has_cta,
					'has_pricing' => $has_pricing,
					'has_schema' => $has_service_schema,
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
		return __('Service Schema', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for Service structured data (service businesses).', 'wpshadow');
	}
}

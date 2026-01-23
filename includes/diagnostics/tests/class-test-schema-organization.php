<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Organization extends Diagnostic_Base
{

	protected static $slug = 'test-schema-organization';
	protected static $title = 'Organization Schema Completeness Test';
	protected static $description = 'Tests Organization schema for completeness';

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
		// Check for Organization schema
		if (!preg_match('/"@type"\s*:\s*"Organization"/i', $html)) {
			return null; // No org schema to check
		}

		// Extract organization JSON-LD
		preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $json_matches);

		if (empty($json_matches)) {
			return null;
		}

		$json_content = $json_matches[1];

		// Check for essential Organization properties
		$has_name = preg_match('/"name"\s*:/i', $json_content);
		$has_url = preg_match('/"url"\s*:/i', $json_content);
		$has_logo = preg_match('/"logo"\s*:/i', $json_content);
		$has_contact = preg_match('/"contactPoint"\s*:/i', $json_content);
		$has_social = preg_match('/"sameAs"\s*:/i', $json_content);

		$missing = [];
		if (!$has_logo) $missing[] = 'logo';
		if (!$has_contact) $missing[] = 'contactPoint';
		if (!$has_social) $missing[] = 'sameAs (social profiles)';

		if (!empty($missing)) {
			return [
				'id' => 'schema-organization-incomplete',
				'title' => 'Organization Schema Incomplete',
				'description' => 'Organization schema found but missing recommended properties: ' . implode(', ', $missing) . '. Complete Organization schema improves knowledge graph presence.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/organization-schema/',
				'training_link' => 'https://wpshadow.com/training/brand-seo/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => [
					'missing_properties' => $missing,
					'has_logo' => $has_logo,
					'has_contact' => $has_contact,
					'has_social' => $has_social,
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
		return __('Organization Schema Completeness', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks Organization schema for recommended properties.', 'wpshadow');
	}
}

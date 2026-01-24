<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Preconnect_DNS_Prefetch extends Diagnostic_Base
{

	protected static $slug = 'test-performance-preconnect';
	protected static $title = 'Preconnect/DNS-Prefetch Test';
	protected static $description = 'Tests for resource hints (preconnect, dns-prefetch)';

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
		// Find external resources
		preg_match_all('/(src|href)=["\']https?:\/\/([^"\'\/]+)/i', $html, $external);
		$external_hosts = array_unique($external[2]);

		// Exclude current site
		$site_host = wp_parse_url(home_url(), PHP_URL_HOST);
		$external_hosts = array_diff($external_hosts, [$site_host]);

		if (count($external_hosts) === 0) {
			return null; // No external resources
		}

		// Check for preconnect/dns-prefetch
		$has_preconnect = preg_match('/<link[^>]+rel=["\']preconnect["\']/i', $html);
		$has_dns_prefetch = preg_match('/<link[^>]+rel=["\']dns-prefetch["\']/i', $html);

		if ($has_preconnect || $has_dns_prefetch) {
			return null; // PASS - has resource hints
		}

		// Common third-party domains that benefit from preconnect
		$major_third_parties = ['fonts.googleapis.com', 'fonts.gstatic.com', 'www.google-analytics.com', 'www.googletagmanager.com', 'cdn.jsdelivr.net'];
		$found_major = array_intersect($external_hosts, $major_third_parties);

		if (empty($found_major)) {
			return null; // No major third-parties
		}

		return [
			'id' => 'performance-preconnect',
			'title' => 'Missing Resource Hints',
			'description' => sprintf(
				'Page loads resources from %d external domains but lacks preconnect/dns-prefetch hints. Adding these can reduce connection time by up to 300ms.',
				count($found_major)
			)
			'kb_link' => 'https://wpshadow.com/kb/resource-hints/',
			'training_link' => 'https://wpshadow.com/training/performance-optimization/',
			'auto_fixable' => false,
			'threat_level' => 35,
			'module' => 'Performance',
			'priority' => 3,
			'meta' => [
				'external_hosts' => array_values($found_major),
				'checked_url' => $checked_url,
			],
		];
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Preconnect/DNS-Prefetch', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for resource hints to speed up external connections.', 'wpshadow');
	}
}

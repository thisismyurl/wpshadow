<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Third_Party_Scripts extends Diagnostic_Base
{

	protected static $slug = 'test-performance-third-party-scripts';
	protected static $title = 'Third-Party Scripts Test';
	protected static $description = 'Tests for excessive third-party JavaScript';

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
		$site_domain = parse_url(home_url('/'), PHP_URL_HOST);

		// Find all script sources
		preg_match_all('/<script[^>]+src=["\']([^"\']+)["\']/i', $html, $scripts);

		$third_party_scripts = [];
		$third_party_services = [];

		foreach ($scripts[1] as $script_src) {
			$script_domain = parse_url($script_src, PHP_URL_HOST);

			if ($script_domain && $script_domain !== $site_domain && strpos($script_domain, $site_domain) === false) {
				$third_party_scripts[] = $script_src;

				// Identify known services
				if (strpos($script_domain, 'google') !== false) {
					$third_party_services[] = 'Google';
				} elseif (strpos($script_domain, 'facebook') !== false || strpos($script_domain, 'fbcdn') !== false) {
					$third_party_services[] = 'Facebook';
				} elseif (strpos($script_domain, 'twitter') !== false) {
					$third_party_services[] = 'Twitter';
				} elseif (strpos($script_domain, 'analytics') !== false) {
					$third_party_services[] = 'Analytics';
				}
			}
		}

		$third_party_count = count($third_party_scripts);
		$third_party_services = array_unique($third_party_services);

		if ($third_party_count > 5) {
			return [
				'id' => 'performance-excessive-third-party',
				'title' => 'Excessive Third-Party Scripts',
				'description' => sprintf(
					'%d third-party scripts detected (%s). Each adds latency and can block rendering.',
					$third_party_count,
					!empty($third_party_services) ? implode(', ', $third_party_services) : 'various services'
				)
				'kb_link' => 'https://wpshadow.com/kb/third-party-scripts/',
				'training_link' => 'https://wpshadow.com/training/script-optimization/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'Performance',
				'priority' => 2,
				'meta' => ['third_party_count' => $third_party_count, 'services' => $third_party_services],
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
		return __('Third-Party Scripts', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for excessive third-party JavaScript.', 'wpshadow');
	}
}

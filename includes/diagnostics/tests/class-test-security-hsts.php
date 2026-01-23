<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_HSTS extends Diagnostic_Base
{

	protected static $slug = 'test-security-hsts';
	protected static $title = 'HSTS Header Test';
	protected static $description = 'Tests for HTTP Strict Transport Security header';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');

		// Only applicable for HTTPS sites
		if (strpos($check_url, 'https://') !== 0) {
			return null;
		}

		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		$has_hsts = isset($headers['strict-transport-security']);

		if (!$has_hsts) {
			return [
				'id' => 'security-no-hsts',
				'title' => 'No HSTS Header',
				'description' => 'HTTPS site without Strict-Transport-Security header. HSTS prevents protocol downgrade attacks and cookie hijacking.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/hsts-header/',
				'training_link' => 'https://wpshadow.com/training/https-hardening/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'module' => 'Security',
				'priority' => 2,
				'meta' => ['has_hsts' => false],
			];
		}

		// Check HSTS configuration
		$hsts_value = $headers['strict-transport-security'];

		// Extract max-age
		if (preg_match('/max-age=([0-9]+)/i', $hsts_value, $match)) {
			$max_age = (int)$match[1];

			// Less than 6 months (15552000 seconds) is not recommended
			if ($max_age < 15552000) {
				return [
					'id' => 'security-hsts-too-short',
					'title' => 'HSTS Duration Too Short',
					'description' => sprintf(
						'HSTS max-age is %d days. Recommended: at least 6 months (180 days) for effective protection.',
						round($max_age / 86400)
					),
					'color' => '#2196f3',
					'bg_color' => '#e3f2fd',
					'kb_link' => 'https://wpshadow.com/kb/hsts-duration/',
					'training_link' => 'https://wpshadow.com/training/https-hardening/',
					'auto_fixable' => false,
					'threat_level' => 30,
					'module' => 'Security',
					'priority' => 3,
					'meta' => ['max_age_days' => round($max_age / 86400)],
				];
			}
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('HSTS Header', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for HTTP Strict Transport Security header.', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_HSTS_Duration extends Diagnostic_Base
{

	protected static $slug = 'test-security-hsts-duration';
	protected static $title = 'HSTS Duration Test';
	protected static $description = 'Tests that HSTS max-age meets recommended duration.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');

		if (strpos($check_url, 'https://') !== 0) {
			return null;
		}

		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		if (!isset($headers['strict-transport-security'])) {
			return null; // Missing header handled by HSTS presence test.
		}

		$hsts_value = $headers['strict-transport-security'];

		if (preg_match('/max-age=([0-9]+)/i', $hsts_value, $match)) {
			$max_age = (int) $match[1];

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
		return __('HSTS Duration', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks that HSTS max-age is at least 6 months.', 'wpshadow');
	}
}

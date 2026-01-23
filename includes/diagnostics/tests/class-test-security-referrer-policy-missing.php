<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Referrer_Policy_Missing extends Diagnostic_Base
{

	protected static $slug = 'test-security-referrer-policy-missing';
	protected static $title = 'Referrer Policy Missing Test';
	protected static $description = 'Tests for absence of Referrer-Policy header.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');
		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);
		$has_referrer_policy = isset($headers['referrer-policy']);

		if (!$has_referrer_policy) {
			return [
				'id' => 'security-no-referrer-policy',
				'title' => 'No Referrer Policy',
				'description' => 'No Referrer-Policy header found. Referrer policy controls what information is sent to external sites, protecting user privacy.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/referrer-policy/',
				'training_link' => 'https://wpshadow.com/training/privacy-headers/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'Security',
				'priority' => 3,
				'meta' => ['has_policy' => false],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Referrer Policy Missing', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for presence of the Referrer-Policy header.', 'wpshadow');
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Referrer_Policy extends Diagnostic_Base
{

	protected static $slug = 'test-security-referrer-policy';
	protected static $title = 'Referrer Policy Test';
	protected static $description = 'Tests for Referrer-Policy header';

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

		// Check for unsafe policies
		$policy_value = strtolower($headers['referrer-policy']);
		if (in_array($policy_value, ['unsafe-url', 'no-referrer-when-downgrade'])) {
			return [
				'id' => 'security-unsafe-referrer-policy',
				'title' => 'Unsafe Referrer Policy',
				'description' => sprintf('Referrer-Policy is "%s" which may leak sensitive URL data. Recommended: "strict-origin-when-cross-origin" or "no-referrer".', $policy_value),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/referrer-policy-values/',
				'training_link' => 'https://wpshadow.com/training/privacy-headers/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Security',
				'priority' => 3,
				'meta' => ['policy_value' => $policy_value],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Referrer Policy', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for Referrer-Policy header.', 'wpshadow');
	}
}

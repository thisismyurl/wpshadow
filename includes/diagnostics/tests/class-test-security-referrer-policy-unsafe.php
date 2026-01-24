<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Referrer_Policy_Unsafe extends Diagnostic_Base
{

	protected static $slug = 'test-security-referrer-policy-unsafe';
	protected static $title = 'Referrer Policy Safety Test';
	protected static $description = 'Tests for unsafe Referrer-Policy values.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');
		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		if (!isset($headers['referrer-policy'])) {
			return null; // Missing header handled separately
		}

		$policy_value = strtolower($headers['referrer-policy']);

		if (in_array($policy_value, ['unsafe-url', 'no-referrer-when-downgrade'])) {
			return [
				'id' => 'security-unsafe-referrer-policy',
				'title' => 'Unsafe Referrer Policy',
				'description' => sprintf('Referrer-Policy is "%s" which may leak sensitive URL data. Recommended: "strict-origin-when-cross-origin" or "no-referrer".', $policy_value)
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
		return __('Referrer Policy Safety', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for unsafe Referrer-Policy values.', 'wpshadow');
	}
}

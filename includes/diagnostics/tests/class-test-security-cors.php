<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_CORS extends Diagnostic_Base
{

	protected static $slug = 'test-security-cors';
	protected static $title = 'CORS Configuration Test';
	protected static $description = 'Tests for overly permissive CORS headers';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');

		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		// Check for wildcard CORS
		if (isset($headers['access-control-allow-origin'])) {
			$cors_value = $headers['access-control-allow-origin'];

			if ($cors_value === '*') {
				// Check if credentials are also allowed (very dangerous combination)
				$allows_credentials = isset($headers['access-control-allow-credentials']) &&
					$headers['access-control-allow-credentials'] === 'true';

				if ($allows_credentials) {
					return [
						'id' => 'security-cors-wildcard-credentials',
						'title' => 'Dangerous CORS Configuration',
						'description' => 'Access-Control-Allow-Origin: * with credentials enabled. This is a critical security vulnerability allowing any site to read your data.',
						'color' => '#f44336',
						'bg_color' => '#ffebee',
						'kb_link' => 'https://wpshadow.com/kb/cors-security/',
						'training_link' => 'https://wpshadow.com/training/api-security/',
						'auto_fixable' => false,
						'threat_level' => 80,
						'module' => 'Security',
						'priority' => 1,
						'meta' => ['cors_value' => '*', 'allows_credentials' => true],
					];
				}

				return [
					'id' => 'security-cors-wildcard',
					'title' => 'Permissive CORS Policy',
					'description' => 'Access-Control-Allow-Origin: * allows any domain to access your resources. Consider restricting to specific domains.',
					'color' => '#ff9800',
					'bg_color' => '#fff3e0',
					'kb_link' => 'https://wpshadow.com/kb/cors-configuration/',
					'training_link' => 'https://wpshadow.com/training/api-security/',
					'auto_fixable' => false,
					'threat_level' => 45,
					'module' => 'Security',
					'priority' => 2,
					'meta' => ['cors_value' => '*'],
				];
			}
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('CORS Configuration', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for overly permissive CORS headers.', 'wpshadow');
	}
}

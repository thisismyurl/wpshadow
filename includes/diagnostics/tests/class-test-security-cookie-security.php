<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Cookie_Security extends Diagnostic_Base
{

	protected static $slug = 'test-security-cookie-security';
	protected static $title = 'Cookie Security Test';
	protected static $description = 'Tests for secure cookie attributes (Secure, HttpOnly, SameSite)';

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

		// Check Set-Cookie headers
		if (isset($headers['set-cookie'])) {
			$cookies = is_array($headers['set-cookie']) ? $headers['set-cookie'] : [$headers['set-cookie']];

			$insecure_cookies = [];

			foreach ($cookies as $cookie) {
				$cookie_lower = strtolower($cookie);

				// Check for missing Secure flag on HTTPS
				if (strpos($cookie_lower, 'secure') === false) {
					$insecure_cookies[] = 'missing Secure flag';
				}

				// Check for missing HttpOnly
				if (strpos($cookie_lower, 'httponly') === false) {
					$insecure_cookies[] = 'missing HttpOnly';
				}

				// Check for missing SameSite
				if (strpos($cookie_lower, 'samesite') === false) {
					$insecure_cookies[] = 'missing SameSite';
				}
			}

			if (!empty($insecure_cookies)) {
				$issues = array_unique($insecure_cookies);

				return [
					'id' => 'security-insecure-cookies',
					'title' => 'Insecure Cookie Configuration',
					'description' => sprintf(
						'Cookies detected with security issues: %s. Insecure cookies are vulnerable to theft and CSRF attacks.',
						implode(', ', $issues)
					)
					'kb_link' => 'https://wpshadow.com/kb/cookie-security/',
					'training_link' => 'https://wpshadow.com/training/session-security/',
					'auto_fixable' => false,
					'threat_level' => 50,
					'module' => 'Security',
					'priority' => 2,
					'meta' => ['issues' => $issues, 'cookie_count' => count($cookies)],
				];
			}
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Cookie Security', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for secure cookie attributes (Secure, HttpOnly, SameSite).', 'wpshadow');
	}
}

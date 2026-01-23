<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_CSP_Header extends Diagnostic_Base
{

	protected static $slug = 'test-security-csp-header';
	protected static $title = 'Content Security Policy Test';
	protected static $description = 'Tests for Content-Security-Policy header';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');

		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		$has_csp = isset($headers['content-security-policy']);
		$has_csp_report = isset($headers['content-security-policy-report-only']);

		if (!$has_csp && !$has_csp_report) {
			return [
				'id' => 'security-no-csp',
				'title' => 'No Content Security Policy',
				'description' => 'No Content-Security-Policy header found. CSP protects against XSS, clickjacking, and code injection attacks.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/content-security-policy/',
				'training_link' => 'https://wpshadow.com/training/security-headers/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'Security',
				'priority' => 2,
				'meta' => ['has_csp' => false],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Content Security Policy', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for Content-Security-Policy header.', 'wpshadow');
	}
}

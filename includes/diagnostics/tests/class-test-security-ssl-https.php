<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_SSL_HTTPS extends Diagnostic_Base
{

	protected static $slug = 'test-security-ssl-https';
	protected static $title = 'SSL/HTTPS Test';
	protected static $description = 'Tests for secure HTTPS connection';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$site_url = $url ?? home_url('/');
		$parsed = wp_parse_url($site_url);

		if (!isset($parsed['scheme']) || $parsed['scheme'] !== 'https') {
			return [
				'id' => 'security-ssl-https',
				'title' => 'Missing HTTPS/SSL',
				'description' => 'Your site is not using HTTPS. Modern browsers mark HTTP sites as "Not Secure" which hurts trust and rankings. HTTPS is also a Google ranking factor.',
				'color' => '#ff5722',
				'bg_color' => '#ffebee',
				'kb_link' => 'https://wpshadow.com/kb/ssl-https/',
				'training_link' => 'https://wpshadow.com/training/security-basics/',
				'auto_fixable' => false,
				'threat_level' => 80,
				'module' => 'Security',
				'priority' => 1,
				'meta' => ['current_scheme' => $parsed['scheme'] ?? 'unknown'],
			];
		}

		// Check for mixed content if HTML provided
		if ($html !== null) {
			$has_mixed_content = preg_match('/src=["\']http:\/\//i', $html) ||
				preg_match('/href=["\']http:\/\//i', $html);

			if ($has_mixed_content) {
				return [
					'id' => 'security-ssl-https',
					'title' => 'Mixed Content Detected',
					'description' => 'Your site uses HTTPS but loads some resources over HTTP. This triggers browser warnings and security issues.',
					'color' => '#ff9800',
					'bg_color' => '#fff3e0',
					'kb_link' => 'https://wpshadow.com/kb/mixed-content/',
					'training_link' => 'https://wpshadow.com/training/security-basics/',
					'auto_fixable' => false,
					'threat_level' => 60,
					'module' => 'Security',
					'priority' => 1,
					'meta' => ['issue' => 'mixed_content'],
				];
			}
		}

		return null; // PASS - Using HTTPS
	}

	public static function get_name(): string
	{
		return __('SSL/HTTPS Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for secure HTTPS connection and mixed content.', 'wpshadow');
	}
}

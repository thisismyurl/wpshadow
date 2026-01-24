<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_HTTP2 extends Diagnostic_Base
{

	protected static $slug = 'test-performance-http2';
	protected static $title = 'HTTP/2 Support Test';
	protected static $description = 'Tests for HTTP/2 protocol support';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');

		// Use WordPress HTTP API
		$response = wp_remote_get($check_url, [
			'timeout' => 10,
			'sslverify' => false,
			'httpversion' => '2.0'
		]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		// Check if response indicates HTTP/2
		$http_version = isset($headers['version']) ? $headers['version'] : '';

		// Also check via curl if available
		$has_http2 = false;
		if (function_exists('curl_version')) {
			$curl_info = curl_version();
			if (isset($curl_info['features']) && ($curl_info['features'] & CURL_VERSION_HTTP2)) {
				// curl supports HTTP/2, test actual connection
				$ch = curl_init($check_url);
				curl_setopt_array($ch, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_TIMEOUT => 10
				]);

				curl_exec($ch);
				$http_version_used = curl_getinfo($ch, CURLINFO_HTTP_VERSION);
				curl_close($ch);

				// CURL_HTTP_VERSION_2_0 = 3, CURL_HTTP_VERSION_2 = 3
				$has_http2 = ($http_version_used >= 3);
			}
		}

		if (!$has_http2 && strpos($check_url, 'https://') === 0) {
			return [
				'id' => 'performance-no-http2',
				'title' => 'HTTP/2 Not Detected',
				'description' => 'Site is using HTTPS but HTTP/2 not detected. HTTP/2 can reduce load time by 15-30% through multiplexing.'
				'kb_link' => 'https://wpshadow.com/kb/http2/',
				'training_link' => 'https://wpshadow.com/training/server-optimization/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'Performance',
				'priority' => 3,
				'meta' => ['http_version' => $http_version, 'is_https' => true],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('HTTP/2 Support', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for HTTP/2 protocol support.', 'wpshadow');
	}
}

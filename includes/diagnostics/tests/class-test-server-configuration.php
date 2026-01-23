<?php

/**
 * WPShadow System Diagnostic Test: Server Configuration
 *
 * Tests server-level configuration: Apache modules, SSL/TLS, compression,
 * security headers, performance settings.
 *
 * Testable via: apache_get_modules(), $_SERVER headers, server info
 * Can be requested by Guardian: "test-apache-modules", "test-ssl-available", etc.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #7 Ridiculously Good - Optimal server configuration
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Server Configuration
 *
 * Main diagnostic for server-level settings.
 * Can request specific server tests via Guardian.
 *
 * @verified Not yet tested
 */
class Test_Server_Configuration extends Diagnostic_Base
{

	protected static $slug = 'server-configuration';
	protected static $title = 'Server Configuration Status';
	protected static $description = 'Checks server-level settings: Apache modules, SSL, compression, security.';

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		$issues = array();

		// Check Apache modules (if Apache)
		if (function_exists('apache_get_modules')) {
			$modules = apache_get_modules();
			$critical_modules = array('mod_rewrite', 'mod_ssl', 'mod_deflate');
			$missing = array_diff($critical_modules, $modules);

			if (! empty($missing)) {
				$issues[] = array(
					'type' => 'apache-modules',
					'missing' => $missing,
					'impact' => 'Missing modules will prevent proper functionality',
				);
			}
		}

		// Check if HTTPS is available
		$is_https = ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
		if (! $is_https) {
			$issues[] = array(
				'type' => 'https-not-active',
				'current' => 'HTTP',
				'impact' => 'Site is not secure. Enable HTTPS immediately.',
			);
		}

		// Check for security headers
		$security_headers = array('X-Frame-Options', 'X-Content-Type-Options', 'Strict-Transport-Security');
		$missing_headers = array();
		foreach ($security_headers as $header) {
			if (! isset($_SERVER['HTTP_' . str_replace('-', '_', strtoupper($header))])) {
				$missing_headers[] = $header;
			}
		}

		if (! empty($missing_headers)) {
			$issues[] = array(
				'type' => 'security-headers',
				'missing' => $missing_headers,
				'impact' => 'Site vulnerable to clickjacking, MIME sniffing, etc.',
			);
		}

		if (! empty($issues)) {
			return array(
				'id'            => static::$slug . '-suboptimal',
				'title'         => 'Server Configuration Issues Found',
				'description'   => count($issues) . ' server configuration issue(s). Contact hosting provider.',
				'color'         => '#ff9800',
				'bg_color'      => '#fff3e0',
				'kb_link'       => 'https://wpshadow.com/kb/server-configuration/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=server-config',
				'training_link' => 'https://wpshadow.com/training/server-configuration/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
				'module'        => 'System',
				'priority'      => 2,
				'meta'          => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Guardian can request: "test-apache-modules"
	 * Checks: Critical Apache modules are loaded
	 */
	public static function test_apache_modules(): array
	{
		if (! function_exists('apache_get_modules')) {
			return array(
				'passed'  => true,
				'message' => '⚠ Not running on Apache (nginx/LiteSpeed/other detected)',
				'data'    => array(
					'available' => false,
					'note' => 'Test only applies to Apache servers',
				),
			);
		}

		$modules = apache_get_modules();
		$critical = array('mod_rewrite', 'mod_ssl');
		$recommended = array('mod_deflate', 'mod_expires', 'mod_headers', 'mod_setenvif');

		$missing_critical = array_diff($critical, $modules);
		$missing_recommended = array_diff($recommended, $modules);

		$passed = empty($missing_critical);

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ All critical Apache modules loaded"
				: "✗ Missing critical modules: " . implode(', ', $missing_critical),
			'data'    => array(
				'total_modules' => count($modules),
				'critical_modules' => $critical,
				'critical_missing' => $missing_critical,
				'recommended_modules' => $recommended,
				'recommended_missing' => $missing_recommended,
				'all_modules' => $modules,
			),
		);
	}

	/**
	 * Guardian can request: "test-https-available"
	 * Checks: HTTPS is active and configured
	 */
	public static function test_https_available(): array
	{
		$is_https = ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

		return array(
			'passed'  => $is_https,
			'message' => $is_https
				? "✓ HTTPS is active and secure"
				: "✗ Site is not using HTTPS - data is not encrypted!",
			'data'    => array(
				'protocol' => $is_https ? 'HTTPS' : 'HTTP',
				'secure' => $is_https,
				'sslVersion' => $is_https && isset($_SERVER['SSL_PROTOCOL']) ? $_SERVER['SSL_PROTOCOL'] : 'Unknown',
			),
		);
	}

	/**
	 * Guardian can request: "test-security-headers"
	 * Checks: Critical security headers are set
	 */
	public static function test_security_headers(): array
	{
		$headers_to_check = array(
			'X-Frame-Options' => 'HTTP_X_FRAME_OPTIONS',
			'X-Content-Type-Options' => 'HTTP_X_CONTENT_TYPE_OPTIONS',
			'Strict-Transport-Security' => 'HTTP_STRICT_TRANSPORT_SECURITY',
			'Content-Security-Policy' => 'HTTP_CONTENT_SECURITY_POLICY',
			'Referrer-Policy' => 'HTTP_REFERRER_POLICY',
		);

		$present = array();
		$missing = array();

		foreach ($headers_to_check as $header => $server_key) {
			if (isset($_SERVER[$server_key])) {
				$present[] = array('header' => $header, 'value' => $_SERVER[$server_key]);
			} else {
				$missing[] = $header;
			}
		}

		$passed = count($missing) <= 2; // Allow 2 missing (e.g., CSP, Referrer-Policy)

		return array(
			'passed'  => $passed,
			'message' => count($present) . " security headers present, " . count($missing) . " missing",
			'data'    => array(
				'present_headers' => $present,
				'missing_headers' => $missing,
				'score' => round((count($present) / count($headers_to_check)) * 100) . '%',
			),
		);
	}

	/**
	 * Guardian can request: "test-server-software"
	 * Returns server software information
	 */
	public static function test_server_software(): array
	{
		$server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
		$server_api = PHP_SAPI;

		return array(
			'passed'  => true,
			'message' => "Server: {$server_software}",
			'data'    => array(
				'server_software' => $server_software,
				'php_sapi' => $server_api,
				'server_protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
				'http_host' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
			),
		);
	}

	/**
	 * Guardian can request: "test-compression-available"
	 * Checks: gzip/brotli compression available
	 */
	public static function test_compression_available(): array
	{
		$gzip_available = extension_loaded('zlib');
		$brotli_available = extension_loaded('brotli');

		$passed = $gzip_available;

		return array(
			'passed'  => $passed,
			'message' => $gzip_available
				? "✓ Compression available (gzip" . ($brotli_available ? " + brotli" : "") . ")"
				: "✗ No compression available (responses will be 70-80% larger)",
			'data'    => array(
				'gzip' => $gzip_available,
				'brotli' => $brotli_available,
				'compression_available' => $gzip_available || $brotli_available,
			),
		);
	}

	/**
	 * Guardian can request: "test-max-upload-size"
	 * Returns current upload size limits
	 */
	public static function test_max_upload_size(): array
	{
		$upload_max = ini_get('upload_max_filesize');
		$post_max = ini_get('post_max_size');

		$upload_bytes = self::get_bytes_value($upload_max);
		$post_bytes = self::get_bytes_value($post_max);

		$limit = min($upload_bytes, $post_bytes);

		return array(
			'passed'  => true,
			'message' => "Maximum upload size: " . $upload_max,
			'data'    => array(
				'upload_max_filesize' => $upload_max,
				'post_max_size' => $post_max,
				'effective_limit_bytes' => $limit,
				'mismatch' => $upload_bytes !== $post_bytes,
			),
		);
	}

	/**
	 * Helper: Convert ini_get values to bytes
	 */
	private static function get_bytes_value($value): int
	{
		$value = trim($value);
		if ($value === '-1' || strtoupper($value) === 'UNLIMITED') {
			return PHP_INT_MAX;
		}

		$matches = array();
		if (preg_match('/^(\d+)\s*([KMG])B?$/i', $value, $matches)) {
			$size = (int) $matches[1];
			$unit = strtoupper($matches[2]);
			return $size * array('K' => 1024, 'M' => 1024 ** 2, 'G' => 1024 ** 3)[$unit];
		}

		return (int) $value;
	}
}

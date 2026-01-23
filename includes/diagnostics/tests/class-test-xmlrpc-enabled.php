<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: XMLRPC Enabled
 *
 * Detects when XML-RPC is enabled, which exposes additional attack surface.
 * XML-RPC is rarely needed and can be disabled for improved security.
 *
 * @since 1.2.0
 */
class Test_Xmlrpc_Enabled extends Diagnostic_Base
{

	/**
	 * Check if XML-RPC is enabled
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		if (! self::is_xmlrpc_enabled()) {
			return null;
		}

		return [
			'threat_level'    => 45,
			'threat_color'    => 'yellow',
			'passed'          => false,
			'issue'           => 'XML-RPC is enabled - exposes unnecessary attack surface',
			'metadata'        => [
				'xmlrpc_enabled' => true,
				'xmlrpc_url'     => home_url('/xmlrpc.php'),
				'can_disable'    => true,
			],
			'kb_link'         => 'https://wpshadow.com/kb/xmlrpc-security/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-api-security/',
		];
	}

	/**
	 * Guardian Sub-Test: XML-RPC status
	 *
	 * @return array Test result
	 */
	public static function test_xmlrpc_status(): array
	{
		$enabled = self::is_xmlrpc_enabled();

		return [
			'test_name'       => 'XML-RPC Status',
			'enabled'         => $enabled,
			'passed'          => ! $enabled,
			'xmlrpc_url'      => home_url('/xmlrpc.php'),
			'description'     => $enabled ? 'XML-RPC is ENABLED (security risk)' : 'XML-RPC is disabled (secure)',
		];
	}

	/**
	 * Guardian Sub-Test: XML-RPC accessibility
	 *
	 * @return array Test result
	 */
	public static function test_xmlrpc_accessibility(): array
	{
		$xmlrpc_url = home_url('/xmlrpc.php');
		$accessible = self::is_xmlrpc_accessible($xmlrpc_url);

		return [
			'test_name'       => 'XML-RPC Accessibility',
			'xmlrpc_url'      => $xmlrpc_url,
			'accessible'      => $accessible,
			'description'     => $accessible ? 'XML-RPC endpoint is publicly accessible' : 'XML-RPC not accessible',
		];
	}

	/**
	 * Guardian Sub-Test: Pingback method detection
	 *
	 * @return array Test result
	 */
	public static function test_pingback_method(): array
	{
		$enabled = self::is_xmlrpc_enabled();
		$methods = [];

		if ($enabled) {
			$methods = [
				'pingback.ping' => 'Can be used for DDoS attacks',
				'wp.getUsersBlogs' => 'User enumeration risk',
				'wp.getComments' => 'Can access private comments',
			];
		}

		return [
			'test_name'      => 'XML-RPC Methods',
			'enabled'        => $enabled,
			'methods'        => $methods,
			'passed'         => ! $enabled,
			'description'    => $enabled ? sprintf('XML-RPC has %d potentially risky methods', count($methods)) : 'XML-RPC disabled',
		];
	}

	/**
	 * Guardian Sub-Test: Disable recommendation
	 *
	 * @return array Test result
	 */
	public static function test_disable_recommendation(): array
	{
		$enabled = self::is_xmlrpc_enabled();

		if (! $enabled) {
			return [
				'test_name'   => 'XML-RPC Disable Recommendation',
				'status'      => 'Already disabled',
				'description' => 'XML-RPC is already disabled (good)',
			];
		}

		$can_disable = true; // Can always add htaccess rule or .htaccess
		$method = self::get_disable_method();

		return [
			'test_name'      => 'XML-RPC Disable Recommendation',
			'can_disable'    => $can_disable,
			'recommended_method' => $method,
			'description'    => sprintf('Recommend disabling via: %s', $method),
		];
	}

	/**
	 * Check if XML-RPC is enabled
	 *
	 * @return bool
	 */
	private static function is_xmlrpc_enabled(): bool
	{
		// Check if xmlrpc.php file exists
		$xmlrpc_file = ABSPATH . 'xmlrpc.php';
		if (! file_exists($xmlrpc_file)) {
			return false;
		}

		// Check if there's a filter disabling XML-RPC
		if (apply_filters('xmlrpc_enabled', true) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Check if XML-RPC endpoint is accessible
	 *
	 * @param string $url XML-RPC URL
	 * @return bool
	 */
	private static function is_xmlrpc_accessible(string $url): bool
	{
		try {
			$response = wp_remote_head($url);

			if (is_wp_error($response)) {
				return false;
			}

			$status_code = wp_remote_retrieve_response_code($response);
			return $status_code === 200;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Get recommended method to disable XML-RPC
	 *
	 * @return string
	 */
	private static function get_disable_method(): string
	{
		if (self::can_use_htaccess()) {
			return 'Update .htaccess to block /xmlrpc.php';
		}

		if (self::can_add_filter()) {
			return 'Add filter in wp-config.php';
		}

		return 'Use plugin like Disable XML-RPC';
	}

	/**
	 * Check if .htaccess can be used
	 *
	 * @return bool
	 */
	private static function can_use_htaccess(): bool
	{
		$htaccess = ABSPATH . '.htaccess';
		return file_exists($htaccess) && is_writable($htaccess);
	}

	/**
	 * Check if filter can be added
	 *
	 * @return bool
	 */
	private static function can_add_filter(): bool
	{
		$wp_config = ABSPATH . 'wp-config.php';
		return file_exists($wp_config) && is_writable($wp_config);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'XML-RPC Enabled';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if XML-RPC is enabled and recommends disabling for security';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Security';
	}
}

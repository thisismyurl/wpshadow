<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: WP-CLI Not Available
 *
 * Detects when WP-CLI is not installed or not accessible.
 * WP-CLI enables powerful command-line management and automation.
 *
 * @since 1.2.0
 */
class Test_Wp_Cli_Not_Available extends Diagnostic_Base
{

	/**
	 * Check for WP-CLI availability
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		if (defined('WP_CLI') && WP_CLI) {
			return null; // WP-CLI is available
		}

		return [
			'threat_level'    => 30,
			'threat_color'    => 'yellow',
			'passed'          => false,
			'issue'           => 'WP-CLI is not available - limits automation capabilities',
			'metadata'        => [
				'wp_cli_available'  => false,
				'wp_cli_path'       => self::find_wp_cli_path(),
				'can_install'       => self::can_install_wp_cli(),
			],
			'kb_link'         => 'https://wpshadow.com/kb/wp-cli-installation/',
			'training_link'   => 'https://wpshadow.com/training/wp-cli-guide/',
		];
	}

	/**
	 * Guardian Sub-Test: WP-CLI availability
	 *
	 * @return array Test result
	 */
	public static function test_wp_cli_availability(): array
	{
		$available = defined('WP_CLI') && WP_CLI;

		return [
			'test_name'       => 'WP-CLI Availability',
			'available'       => $available,
			'passed'          => $available,
			'description'     => $available ? 'WP-CLI is available' : 'WP-CLI is not available',
		];
	}

	/**
	 * Guardian Sub-Test: WP-CLI version
	 *
	 * @return array Test result
	 */
	public static function test_wp_cli_version(): array
	{
		$version = self::get_wp_cli_version();

		return [
			'test_name'   => 'WP-CLI Version',
			'version'     => $version ?? 'Unknown',
			'description' => $version ? sprintf('WP-CLI version: %s', $version) : 'WP-CLI version unknown',
		];
	}

	/**
	 * Guardian Sub-Test: WP-CLI permissions
	 *
	 * @return array Test result
	 */
	public static function test_wp_cli_permissions(): array
	{
		$can_execute = self::can_execute_wp_cli();

		return [
			'test_name'     => 'WP-CLI Execution',
			'can_execute'   => $can_execute,
			'passed'        => $can_execute,
			'description'   => $can_execute ? 'WP-CLI can be executed' : 'Cannot execute WP-CLI',
		];
	}

	/**
	 * Guardian Sub-Test: Installation recommendation
	 *
	 * @return array Test result
	 */
	public static function test_installation_recommendation(): array
	{
		$available = defined('WP_CLI') && WP_CLI;
		$can_install = self::can_install_wp_cli();

		$recommendation = ! $available && $can_install ? 'WP-CLI can be installed' : ($available ? 'WP-CLI is installed' : 'Contact hosting provider to install WP-CLI');

		return [
			'test_name'         => 'Installation Options',
			'wp_cli_available'  => $available,
			'can_install'       => $can_install,
			'recommendation'    => $recommendation,
			'description'       => $recommendation,
		];
	}

	/**
	 * Find WP-CLI path
	 *
	 * @return string|null WP-CLI path or null
	 */
	private static function find_wp_cli_path(): ?string
	{
		$possible_paths = [
			'/usr/local/bin/wp',
			'/usr/bin/wp',
			'/opt/rh/rh-php*/root/usr/bin/wp',
			defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR . '/wp-cli.phar' : null,
		];

		foreach ($possible_paths as $path) {
			if ($path && file_exists($path) && is_executable($path)) {
				return $path;
			}
		}

		return null;
	}

	/**
	 * Check if WP-CLI can be installed
	 *
	 * @return bool
	 */
	private static function can_install_wp_cli(): bool
	{
		// Check if php command is available
		$php_available = shell_exec('command -v php 2>&1') !== null;

		// Check if curl is available
		$curl_available = shell_exec('command -v curl 2>&1') !== null;

		// Check write permissions on /usr/local/bin
		$can_write_bin = is_writable('/usr/local/bin') || is_writable('/usr/bin');

		return $php_available && $curl_available;
	}

	/**
	 * Get WP-CLI version
	 *
	 * @return string|null WP-CLI version
	 */
	private static function get_wp_cli_version(): ?string
	{
		if (! defined('WP_CLI_VERSION')) {
			return null;
		}

		return WP_CLI_VERSION;
	}

	/**
	 * Check if WP-CLI can be executed
	 *
	 * @return bool
	 */
	private static function can_execute_wp_cli(): bool
	{
		if (! defined('WP_CLI') || ! WP_CLI) {
			return false;
		}

		try {
			// Try a simple WP-CLI command
			if (function_exists('WP_CLI')) {
				return true;
			}
		} catch (\Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'WP-CLI Not Available';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if WP-CLI command-line tool is available for automation';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'System';
	}
}

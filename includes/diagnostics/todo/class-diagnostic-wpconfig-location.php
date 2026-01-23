<?php

declare(strict_types=1);
/**
 * wp-config.php Location Security Diagnostic
 *
 * Philosophy: Security hardening - protect database credentials
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check wp-config.php location and permissions.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_WPConfig_Location extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$location_result = self::detect_wpconfig_location();

		if (! $location_result) {
			return null; // Can't find config
		}

		$config_file = $location_result['path'];
		$is_root     = $location_result['is_root'];

		// Check permissions (should not be world-readable)
		$perms = fileperms($config_file);
		$octal = substr(sprintf('%o', $perms), -3);

		// If world-readable (e.g., 644, 664, 777)
		if (substr($octal, -1) >= '4') {
			$location_text = $is_root ? 'in web root' : 'one level above web root (good security practice)';
			return array(
				'id'            => 'wpconfig-location',
				'title'         => 'wp-config.php Permissions Too Permissive',
				'description'   => 'Your wp-config.php file (' . $location_text . ') has world-readable permissions (' . $octal . '). Set permissions to 600 or 640 to restrict access to database credentials.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/secure-wp-config-permissions/',
				'training_link' => 'https://wpshadow.com/training/wpconfig-security/',
				'auto_fixable'  => false,
				'threat_level'  => 85,
				'config_location' => $location_result,
			);
		}

		return null;
	}

	/**
	 * Detect wp-config.php location
	 *
	 * WordPress allows wp-config.php to be placed in two locations:
	 * 1. Web root (ABSPATH) - default but less secure
	 * 2. One level above web root - more secure (blocks direct access)
	 *
	 * @return ?array Array with 'path' and 'is_root' keys, or null if not found
	 */
	public static function detect_wpconfig_location(): ?array
	{
		$abspath = ABSPATH;

		// Check web root first
		$config_file = $abspath . 'wp-config.php';
		if (file_exists($config_file)) {
			return [
				'path'    => $config_file,
				'is_root' => true,
				'message' => 'wp-config.php found in web root (less secure)',
			];
		}

		// Check one level above web root (more secure)
		$config_file = dirname($abspath) . '/wp-config.php';
		if (file_exists($config_file)) {
			return [
				'path'    => $config_file,
				'is_root' => false,
				'message' => 'wp-config.php found one level above web root (better security)',
			];
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Tests both wp-config.php location detection and permission checking.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__wpconfig_location(): array
	{
		// Detect wp-config.php location
		$location = self::detect_wpconfig_location();

		if (! $location) {
			return [
				'passed' => false,
				'message' => '✗ Could not locate wp-config.php in expected locations',
			];
		}

		// Run the full diagnostic check
		$result = self::check();

		$location_type = $location['is_root']
			? 'web root (less secure)'
			: 'one level above web root (more secure)';

		if (is_null($result)) {
			return [
				'passed' => true,
				'message' => '✓ wp-config.php found in ' . $location_type . ' with secure permissions',
			];
		}

		return [
			'passed' => false,
			'message' => '✗ wp-config.php found in ' . $location_type . ' but has insecure permissions: ' . $result['title'],
		];
	}
}

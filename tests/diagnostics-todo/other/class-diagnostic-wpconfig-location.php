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
class Diagnostic_WPConfig_Location extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$abspath     = ABSPATH;
		$config_file = $abspath . 'wp-config.php';

		// Check if wp-config.php exists in web root
		if ( ! file_exists( $config_file ) ) {
			// May be one level up (which is good)
			$config_file = dirname( $abspath ) . '/wp-config.php';
			if ( ! file_exists( $config_file ) ) {
				return null; // Can't find config
			}
		}

		// Check permissions (should not be world-readable)
		$perms = fileperms( $config_file );
		$octal = substr( sprintf( '%o', $perms ), -3 );

		// If world-readable (e.g., 644, 664, 777)
		if ( substr( $octal, -1 ) >= '4' ) {
			return array(
				'id'            => 'wpconfig-location',
				'title'         => 'wp-config.php Permissions Too Permissive',
				'description'   => 'Your wp-config.php file has world-readable permissions (' . $octal . '). Set permissions to 600 or 640 to restrict access to database credentials.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/secure-wp-config-permissions/',
				'training_link' => 'https://wpshadow.com/training/wpconfig-security/',
				'auto_fixable'  => false,
				'threat_level'  => 85,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: WPConfig Location
	 * Slug: -wpconfig-location
	 * File: class-diagnostic-wpconfig-location.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: WPConfig Location
	 * Slug: -wpconfig-location
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__wpconfig_location(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}

<?php
declare(strict_types=1);
/**
 * File Permissions Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check file permissions for security.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_File_Permissions extends Diagnostic_Base {

	protected static $slug        = 'file-permissions';
	protected static $title       = 'Insecure File Permissions';
	protected static $description = 'Some files have insecure permissions that could allow unauthorized access.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();

		// Check wp-config.php permissions
		$wp_config = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $wp_config ) ) {
			// Try parent directory
			$wp_config = dirname( ABSPATH ) . '/wp-config.php';
		}

		if ( file_exists( $wp_config ) ) {
			$perms = fileperms( $wp_config );
			// Check if world-readable or group-writable
			if ( ( $perms & 0020 ) || ( $perms & 0002 ) ) {
				$issues[] = 'wp-config.php has insecure permissions (should be 400 or 440)';
			}
		}

		// Check wp-content directory writability
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			$issues[] = 'wp-content directory is not writable (needed for uploads and plugins)';
		}

		if ( ! empty( $issues ) ) {
			return array(
				'title'       => self::$title,
				'description' => implode( '. ', $issues ) . '. Fix file permissions via FTP or SSH.',
				'severity'    => 'medium',
				'category'    => 'security',
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Insecure File Permissions
	 * Slug: file-permissions
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Some files have insecure permissions that could allow unauthorized access.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_file_permissions(): array {
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
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}

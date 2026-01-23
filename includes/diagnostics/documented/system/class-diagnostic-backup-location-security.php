<?php
declare(strict_types=1);
/**
 * Backup Location Security Diagnostic
 *
 * Philosophy: Data protection - secure backup storage
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if backups are stored securely outside web root.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Backup_Location_Security extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$upload_dir = wp_upload_dir();
		
		// Check for backup files in web-accessible directory
		$backup_files = glob( $upload_dir['basedir'] . '/*.{zip,tar,gz,sql}', GLOB_BRACE );
		
		if ( ! empty( $backup_files ) ) {
			return array(
				'id'          => 'backup-location-security',
				'title'       => 'Backup Files in Web-Accessible Directory',
				'description' => sprintf(
					'Found %d backup files in uploads directory (web-accessible). Attackers can download complete site backups including database. Store backups outside web root or protect with .htaccess.',
					count( $backup_files )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-backup-storage/',
				'training_link' => 'https://wpshadow.com/training/backup-security/',
				'auto_fixable' => false,
				'threat_level' => 90,
			);
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Backup Location Security
	 * Slug: -backup-location-security
	 * File: class-diagnostic-backup-location-security.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Backup Location Security
	 * Slug: -backup-location-security
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
	public static function test_live__backup_location_security(): array {
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

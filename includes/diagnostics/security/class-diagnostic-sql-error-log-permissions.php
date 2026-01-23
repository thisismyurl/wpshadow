<?php
declare(strict_types=1);
/**
 * SQL Error Log Permissions Diagnostic
 *
 * Philosophy: Log security - protect error logs
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check database error log permissions.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SQL_Error_Log_Permissions extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Try to get error log location
		$log_error = $wpdb->get_var( "SHOW VARIABLES LIKE 'log_error'" );
		
		if ( empty( $log_error ) || $log_error === 'OFF' ) {
			return null;
		}
		
		// Common error log paths
		$common_paths = array(
			'/var/log/mysql/error.log',
			'/var/lib/mysql/error.log',
			'/usr/local/mysql/data/error.log',
		);
		
		foreach ( $common_paths as $path ) {
			if ( file_exists( $path ) ) {
				$perms = fileperms( $path );
				$perms_octal = substr( sprintf( '%o', $perms ), -4 );
				
				// Check if world-readable (others have read permission)
				if ( $perms & 0x0004 ) {
					return array(
						'id'          => 'sql-error-log-permissions',
						'title'       => 'MySQL Error Log World-Readable',
						'description' => sprintf(
							'MySQL error log at %s has permissions %s (world-readable). Error logs contain failed queries with passwords and sensitive data. Restrict to 600 (owner-only).',
							$path,
							$perms_octal
						),
						'severity'    => 'high',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/secure-mysql-logs/',
						'training_link' => 'https://wpshadow.com/training/log-security/',
						'auto_fixable' => false,
						'threat_level' => 70,
					);
				}
			}
		}
		
		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SQL Error Log Permissions
	 * Slug: -sql-error-log-permissions
	 * File: class-diagnostic-sql-error-log-permissions.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SQL Error Log Permissions
	 * Slug: -sql-error-log-permissions
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
	public static function test_live__sql_error_log_permissions(): array {
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

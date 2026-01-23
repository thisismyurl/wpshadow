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
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}

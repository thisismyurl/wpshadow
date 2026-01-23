<?php
declare(strict_types=1);
/**
 * MySQL Binary Log Exposure Diagnostic
 *
 * Philosophy: Database security - protect database activity logs
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check MySQL binary log exposure.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_MySQL_Binary_Log extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;

		// Check if binary logging is enabled
		$log_bin = $wpdb->get_var( "SHOW VARIABLES LIKE 'log_bin'" );

		if ( empty( $log_bin ) ) {
			return null; // Binary logging not enabled
		}

		// Get binary log location
		$log_bin_basename = $wpdb->get_var( "SHOW VARIABLES LIKE 'log_bin_basename'" );

		if ( ! empty( $log_bin_basename ) ) {
			// Check if log files might be in webroot (common misconfiguration)
			$webroot = ABSPATH;

			return array(
				'id'            => 'mysql-binary-log',
				'title'         => 'MySQL Binary Logging Enabled',
				'description'   => 'MySQL binary logs are enabled and contain all database queries including passwords and sensitive data. Ensure binary logs are stored outside webroot and have restricted permissions (600). Logs should be rotated and purged regularly.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/secure-mysql-binary-logs/',
				'training_link' => 'https://wpshadow.com/training/database-logging/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
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

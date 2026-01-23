<?php
declare(strict_types=1);
/**
 * Database Backup Location Diagnostic
 *
 * Philosophy: Backup security - protect database dumps
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if database backups are in webroot.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Database_Backup_Location extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Common backup file patterns
		$patterns = array(
			'*.sql',
			'*.sql.gz',
			'*.sql.zip',
			'*.db',
			'backup*.sql',
			'dump*.sql',
			'database*.sql',
		);
		
		$found_backups = array();
		
		foreach ( $patterns as $pattern ) {
			$files = glob( ABSPATH . $pattern );
			if ( ! empty( $files ) ) {
				foreach ( $files as $file ) {
					$found_backups[] = basename( $file );
				}
			}
			
			// Also check wp-content
			$files = glob( WP_CONTENT_DIR . '/' . $pattern );
			if ( ! empty( $files ) ) {
				foreach ( $files as $file ) {
					$found_backups[] = 'wp-content/' . basename( $file );
				}
			}
		}
		
		if ( ! empty( $found_backups ) ) {
			return array(
				'id'          => 'database-backup-location',
				'title'       => 'Database Backups in Web Root',
				'description' => sprintf(
					'Database backup files found in web-accessible directories: %s. These files contain your entire database including passwords. Move backups outside webroot or delete immediately.',
					implode( ', ', array_slice( $found_backups, 0, 5 ) )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-database-backups/',
				'training_link' => 'https://wpshadow.com/training/backup-security/',
				'auto_fixable' => true,
				'threat_level' => 85,
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

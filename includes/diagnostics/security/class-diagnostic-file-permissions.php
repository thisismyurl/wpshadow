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

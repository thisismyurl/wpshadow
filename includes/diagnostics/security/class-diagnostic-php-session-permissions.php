<?php
declare(strict_types=1);
/**
 * PHP Session Directory Permissions Diagnostic
 *
 * Philosophy: Session security - protect session files
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check PHP session directory permissions.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_PHP_Session_Permissions extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$session_path = session_save_path();
		
		if ( empty( $session_path ) ) {
			$session_path = '/var/lib/php/sessions'; // Common default
		}
		
		if ( ! file_exists( $session_path ) || ! is_dir( $session_path ) ) {
			return null;
		}
		
		$perms = fileperms( $session_path );
		$perms_octal = substr( sprintf( '%o', $perms ), -4 );
		
		// Check if permissions are too open (should be 700 or 1733 with sticky bit)
		if ( ( $perms & 0x0004 ) || ( $perms & 0x0002 ) ) {
			// World-readable or world-writable
			return array(
				'id'          => 'php-session-permissions',
				'title'       => 'Insecure PHP Session Directory',
				'description' => sprintf(
					'PHP session directory %s has insecure permissions (%s). Other users can read all sessions, hijacking accounts on shared hosting. Set permissions to 700 or 1733.',
					$session_path,
					$perms_octal
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-php-sessions/',
				'training_link' => 'https://wpshadow.com/training/session-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
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

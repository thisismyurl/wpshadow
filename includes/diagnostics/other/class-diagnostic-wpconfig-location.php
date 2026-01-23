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

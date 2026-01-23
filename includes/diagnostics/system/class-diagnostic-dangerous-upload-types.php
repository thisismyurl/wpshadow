<?php
declare(strict_types=1);
/**
 * Dangerous File Upload Types Diagnostic
 *
 * Philosophy: File security - validate upload file types
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for dangerous file upload types allowed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Dangerous_Upload_Types extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$allowed_types = get_allowed_mime_types();
		
		$dangerous = array( 'php', 'phtml', 'php5', 'php7', 'phar', 'exe', 'sh', 'bat', 'cmd' );
		$found_dangerous = array();
		
		foreach ( $allowed_types as $ext => $mime ) {
			foreach ( $dangerous as $bad_ext ) {
				if ( preg_match( '/' . preg_quote( $bad_ext, '/' ) . '/i', $ext ) ) {
					$found_dangerous[] = $ext;
				}
			}
		}
		
		if ( ! empty( $found_dangerous ) ) {
			return array(
				'id'          => 'dangerous-upload-types',
				'title'       => 'Dangerous File Types Allowed for Upload',
				'description' => sprintf(
					'Dangerous file types allowed: %s. Uploading executable files allows code execution. Restrict uploads to safe types (jpg, png, pdf, doc).',
					implode( ', ', array_slice( $found_dangerous, 0, 3 ) )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/restrict-upload-file-types/',
				'training_link' => 'https://wpshadow.com/training/upload-security/',
				'auto_fixable' => false,
				'threat_level' => 90,
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

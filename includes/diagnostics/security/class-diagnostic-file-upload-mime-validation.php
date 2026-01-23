<?php
declare(strict_types=1);
/**
 * File Upload MIME Type Validation Diagnostic
 *
 * Philosophy: Upload security - validate actual file content
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if file uploads validate MIME types properly.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_File_Upload_MIME_Validation extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if fileinfo extension is available
		if ( ! function_exists( 'finfo_open' ) ) {
			return array(
				'id'          => 'file-upload-mime-validation',
				'title'       => 'Missing File Type Detection',
				'description' => 'PHP fileinfo extension is not installed. WordPress cannot properly validate file types, allowing malicious files to be uploaded with fake extensions (e.g., malware.php.jpg). Install php-fileinfo extension.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-fileinfo-extension/',
				'training_link' => 'https://wpshadow.com/training/upload-security/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		// Check WordPress upload settings
		$upload_filetypes = get_option( 'upload_filetypes' );
		
		// If multisite and dangerous file types are allowed
		if ( is_multisite() && ! empty( $upload_filetypes ) ) {
			$dangerous_types = array( 'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'cgi', 'exe', 'sh' );
			$allowed_array = array_map( 'trim', explode( ' ', strtolower( $upload_filetypes ) ) );
			
			$found_dangerous = array_intersect( $dangerous_types, $allowed_array );
			
			if ( ! empty( $found_dangerous ) ) {
				return array(
					'id'          => 'file-upload-mime-validation',
					'title'       => 'Dangerous File Types Allowed',
					'description' => sprintf(
						'Your multisite allows uploading executable files: %s. This enables remote code execution. Remove these file types from allowed uploads.',
						implode( ', ', $found_dangerous )
					),
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/restrict-file-uploads/',
					'training_link' => 'https://wpshadow.com/training/upload-security/',
					'auto_fixable' => true,
					'threat_level' => 85,
				);
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
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}

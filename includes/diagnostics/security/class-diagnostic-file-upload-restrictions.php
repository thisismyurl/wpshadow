<?php
declare(strict_types=1);
/**
 * File Upload Restrictions Diagnostic
 *
 * Philosophy: Security hardening - block malicious uploads
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if dangerous file types are blocked from uploads.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_File_Upload_Restrictions extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Get allowed mime types
		$mimes = get_allowed_mime_types();
		
		// Check for dangerous extensions
		$dangerous = array( 'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'exe', 'com', 'bat', 'sh' );
		$allowed_dangerous = array();
		
		foreach ( $mimes as $ext => $mime ) {
			$exts = explode( '|', $ext );
			foreach ( $exts as $single_ext ) {
				if ( in_array( $single_ext, $dangerous, true ) ) {
					$allowed_dangerous[] = $single_ext;
				}
			}
		}
		
		if ( ! empty( $allowed_dangerous ) ) {
			return array(
				'id'          => 'file-upload-restrictions',
				'title'       => 'Dangerous File Types Allowed',
				'description' => sprintf(
					'Your site allows uploading dangerous file types: %s. Block these extensions to prevent malicious file execution.',
					implode( ', ', $allowed_dangerous )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/block-dangerous-uploads/',
				'training_link' => 'https://wpshadow.com/training/file-upload-security/',
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

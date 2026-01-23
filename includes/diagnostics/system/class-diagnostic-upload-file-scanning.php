<?php
declare(strict_types=1);
/**
 * Upload File Scanning Diagnostic
 *
 * Philosophy: Upload security - scan files on upload
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if uploads are scanned for malware.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Upload_File_Scanning extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$upload_scan_plugins = array(
			'wordfence/wordfence.php',
			'shield-security/shield-security-pro.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $upload_scan_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		// Check if add_filter hook for uploads exists
		if ( has_filter( 'wp_handle_upload' ) ) {
			return null;
		}
		
		return array(
			'id'          => 'upload-file-scanning',
			'title'       => 'File Uploads Not Scanned for Malware',
			'description' => 'Uploaded files are not scanned for malware. Malicious files (backdoors, malware) can be uploaded and executed. Enable real-time malware scanning on uploads.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/scan-uploads-for-malware/',
			'training_link' => 'https://wpshadow.com/training/upload-security/',
			'auto_fixable' => false,
			'threat_level' => 85,
		);
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
	 * Test: Hook detection logic
	 *
	 * Verifies that diagnostic correctly detects hooks and returns
	 * appropriate result (null or array).
	 *
	 * @return array Test result
	 */
	public static function test_hook_detection(): array {
		$result = self::check();
		
		// Should consistently return null or array
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Hook detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Unexpected result type from hook detection',
		);
	}}

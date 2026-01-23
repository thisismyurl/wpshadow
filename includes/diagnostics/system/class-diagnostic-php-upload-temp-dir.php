<?php
declare(strict_types=1);
/**
 * PHP Upload Temp Directory Diagnostic
 *
 * Philosophy: File security - clean temporary files
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check PHP upload temp directory for old files.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_PHP_Upload_Temp_Dir extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$upload_tmp_dir = ini_get( 'upload_tmp_dir' );
		
		if ( empty( $upload_tmp_dir ) ) {
			$upload_tmp_dir = sys_get_temp_dir();
		}
		
		if ( ! is_dir( $upload_tmp_dir ) || ! is_readable( $upload_tmp_dir ) ) {
			return null;
		}
		
		// Count PHP temp files
		$pattern = $upload_tmp_dir . '/php*';
		$temp_files = glob( $pattern );
		
		if ( empty( $temp_files ) ) {
			return null;
		}
		
		$old_files = 0;
		$total_size = 0;
		
		foreach ( $temp_files as $file ) {
			if ( is_file( $file ) ) {
				$age = time() - filemtime( $file );
				$total_size += filesize( $file );
				
				// Files older than 1 hour
				if ( $age > 3600 ) {
					$old_files++;
				}
			}
		}
		
		if ( $old_files > 50 ) {
			return array(
				'id'          => 'php-upload-temp-dir',
				'title'       => 'Excessive Old Temporary Upload Files',
				'description' => sprintf(
					'Found %d temporary upload files older than 1 hour in %s (total: %s). Temp files should auto-delete but aren\'t. Old malware uploads may persist. Configure proper temp file cleanup.',
					$old_files,
					$upload_tmp_dir,
					size_format( $total_size )
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/clean-temp-files/',
				'training_link' => 'https://wpshadow.com/training/temp-file-security/',
				'auto_fixable' => true,
				'threat_level' => 70,
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

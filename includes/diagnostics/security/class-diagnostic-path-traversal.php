<?php
declare(strict_types=1);
/**
 * Path Traversal Vulnerability Diagnostic
 *
 * Philosophy: File security - prevent directory escape
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for path traversal vulnerabilities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Path_Traversal extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$plugins_dir = WP_PLUGIN_DIR;
		$files = glob( $plugins_dir . '/*/*.php' );
		
		foreach ( $files as $file ) {
			$content = file_get_contents( $file );
			
			// Look for file operations with user input without sanitization
			if ( preg_match( '/file_get_contents\s*\(\s*\$_(?:GET|POST|REQUEST)|file_exists\s*\(\s*\$_|is_file\s*\(\s*\$_/', $content ) ) {
				if ( ! preg_match( '/sanitize_file_name|basename|preg_replace.*\.\./', $content ) ) {
					return array(
						'id'          => 'path-traversal',
						'title'       => 'Path Traversal Vulnerability',
						'description' => 'Code uses user input in file paths without sanitization. Attackers can use ../ to escape directory. Use basename() and sanitize file names.',
						'severity'    => 'critical',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/prevent-path-traversal/',
						'training_link' => 'https://wpshadow.com/training/file-path-safety/',
						'auto_fixable' => false,
						'threat_level' => 90,
					);
				}
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
	}}

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

}
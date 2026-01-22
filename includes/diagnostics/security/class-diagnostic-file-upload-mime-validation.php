<?php
declare(strict_types=1);
/**
 * File Upload MIME Type Validation Diagnostic
 *
 * Philosophy: Upload security - validate actual file content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if file uploads validate MIME types properly.
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
}

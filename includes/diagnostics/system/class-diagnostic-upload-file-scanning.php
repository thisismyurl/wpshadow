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

}
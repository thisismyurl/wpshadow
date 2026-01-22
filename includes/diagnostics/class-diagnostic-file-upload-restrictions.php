<?php declare(strict_types=1);
/**
 * File Upload Restrictions Diagnostic
 *
 * Philosophy: Security hardening - block malicious uploads
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if dangerous file types are blocked from uploads.
 */
class Diagnostic_File_Upload_Restrictions {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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
}

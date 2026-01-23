<?php
declare(strict_types=1);
/**
 * Backdoor Detection Diagnostic
 *
 * Philosophy: Intrusion detection - identify web shells
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for web shells and backdoors.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Backdoor_Detection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Scan for common backdoor patterns in uploads
		$upload_dir = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];
		
		if ( ! is_dir( $uploads_path ) ) {
			return null;
		}
		
		// Check for suspicious files in uploads (quick scan)
		$files = glob( $uploads_path . '/*.php' );
		
		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					$content = file_get_contents( $file );
					
					// Look for shell patterns
					if ( preg_match( '/exec|passthru|shell_exec|system|popen|proc_open|eval|base64_decode|create_function/i', $content ) ) {
						return array(
							'id'          => 'backdoor-detection',
							'title'       => 'Potential Backdoor/Web Shell Found',
							'description' => sprintf(
								'Suspicious PHP file detected in uploads directory: %s. This may be a web shell or backdoor. Remove immediately and restore from clean backup.',
								basename( $file )
							),
							'severity'    => 'critical',
							'category'    => 'security',
							'kb_link'     => 'https://wpshadow.com/kb/remove-web-shells/',
							'training_link' => 'https://wpshadow.com/training/backdoor-removal/',
							'auto_fixable' => false,
							'threat_level' => 95,
						);
					}
				}
			}
		}
		
		return null;
	}

}
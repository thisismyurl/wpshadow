<?php
declare(strict_types=1);
/**
 * File Integrity Monitoring Diagnostic
 *
 * Philosophy: Intrusion detection - detect compromised files
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if file integrity monitoring is active.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_File_Integrity_Monitoring extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$fim_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
			'shield-security/shield-security-pro.php',
		);

		$active = get_option( 'active_plugins', array() );
		foreach ( $fim_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}

		return array(
			'id'            => 'file-integrity-monitoring',
			'title'         => 'No File Integrity Monitoring',
			'description'   => 'File changes go undetected. Malware and backdoors can be added without your knowledge. Enable file integrity monitoring to detect unauthorized file modifications.',
			'severity'      => 'high',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/enable-file-integrity-monitoring/',
			'training_link' => 'https://wpshadow.com/training/file-security/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
		);
	}
}

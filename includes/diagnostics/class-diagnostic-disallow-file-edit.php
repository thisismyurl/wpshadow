<?php declare(strict_types=1);
/**
 * File Editor Hardening Diagnostic
 *
 * Philosophy: Security best practice; educates on reducing attack surface.
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if DISALLOW_FILE_EDIT is enabled to block theme/plugin editor.
 */
class Diagnostic_Disallow_File_Edit {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
			return null; // Already hardened
		}
		
		return array(
			'title'       => 'Theme/Plugin File Editor Enabled',
			'description' => 'Built-in file editor is enabled. Disable it to reduce risk of code tampering.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/disable-wordpress-file-editor/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=file-editor',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}
}

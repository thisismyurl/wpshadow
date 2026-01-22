<?php
declare(strict_types=1);
/**
 * PHP Session Directory Permissions Diagnostic
 *
 * Philosophy: Session security - protect session files
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check PHP session directory permissions.
 */
class Diagnostic_PHP_Session_Permissions extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$session_path = session_save_path();
		
		if ( empty( $session_path ) ) {
			$session_path = '/var/lib/php/sessions'; // Common default
		}
		
		if ( ! file_exists( $session_path ) || ! is_dir( $session_path ) ) {
			return null;
		}
		
		$perms = fileperms( $session_path );
		$perms_octal = substr( sprintf( '%o', $perms ), -4 );
		
		// Check if permissions are too open (should be 700 or 1733 with sticky bit)
		if ( ( $perms & 0x0004 ) || ( $perms & 0x0002 ) ) {
			// World-readable or world-writable
			return array(
				'id'          => 'php-session-permissions',
				'title'       => 'Insecure PHP Session Directory',
				'description' => sprintf(
					'PHP session directory %s has insecure permissions (%s). Other users can read all sessions, hijacking accounts on shared hosting. Set permissions to 700 or 1733.',
					$session_path,
					$perms_octal
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-php-sessions/',
				'training_link' => 'https://wpshadow.com/training/session-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}

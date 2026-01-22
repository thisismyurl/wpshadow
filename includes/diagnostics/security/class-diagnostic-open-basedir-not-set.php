<?php
declare(strict_types=1);
/**
 * Open Basedir Restriction Not Set Diagnostic
 *
 * Philosophy: Server hardening - restrict file access scope
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if open_basedir is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Open_Basedir_Not_Set extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$open_basedir = ini_get( 'open_basedir' );
		
		if ( empty( $open_basedir ) ) {
			return array(
				'id'          => 'open-basedir-not-set',
				'title'       => 'open_basedir Not Restricted',
				'description' => 'PHP can access entire file system. Compromised code can read any file. Configure php.ini open_basedir to restrict access to WordPress directory only.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-open-basedir/',
				'training_link' => 'https://wpshadow.com/training/php-hardening/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}

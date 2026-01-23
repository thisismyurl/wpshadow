<?php
declare(strict_types=1);
/**
 * Automated Backup & Recovery System Diagnostic
 *
 * Philosophy: Disaster recovery - automated backup restoration
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if automated backups with recovery are configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Automated_Backup_Recovery extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'jetpack/jetpack.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $backup_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'automated-backup-recovery',
			'title'       => 'No Automated Backups with Recovery',
			'description' => 'No automated backup system. If compromised or corrupted, you cannot recover. Enable daily automated backups with tested recovery procedures.',
			'severity'    => 'critical',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/set-up-automated-backups/',
			'training_link' => 'https://wpshadow.com/training/backup-recovery/',
			'auto_fixable' => false,
			'threat_level' => 90,
		);
	}

}
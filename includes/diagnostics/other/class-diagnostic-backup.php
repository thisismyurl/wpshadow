<?php
declare(strict_types=1);
/**
 * Backup Plugin Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for active backup solution.
 */
class Diagnostic_Backup extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::has_backup_plugin() ) {
			return array(
				'id'           => 'backup-missing',
				'title'        => 'No Backup Solution Detected',
				'description'  => 'Your site has no automated backup plugin active. Regular backups are critical for recovery.',
				'color'        => '#f44336',
				'bg_color'     => '#ffebee',
				'kb_link'      => 'https://wpshadow.com/kb/how-to-set-up-automated-backups/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=backup',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}
		
		return null;
	}
	
	/**
	 * Check if a backup plugin is active.
	 *
	 * @return bool True if backup plugin detected.
	 */
	private static function has_backup_plugin() {
		$backup_keywords = array( 'backup', 'updraft', 'backwpup', 'duplicator', 'snapshot', 'vaultpress', 'jetpack' );
		$active_plugins  = get_option( 'active_plugins', array() );
		
		foreach ( $active_plugins as $plugin ) {
			$plugin_lower = strtolower( $plugin );
			foreach ( $backup_keywords as $keyword ) {
				if ( false !== strpos( $plugin_lower, $keyword ) ) {
					return true;
				}
			}
		}
		
		return false;
	}
}

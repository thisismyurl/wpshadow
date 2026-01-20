<?php
/**
 * Outdated Plugins Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Check for outdated plugins.
 */
class Diagnostic_Outdated_Plugins {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$outdated = self::get_outdated_plugins_count();
		
		if ( $outdated > 0 ) {
			return array(
				'id'           => 'outdated-plugins',
				'title'        => "You Have {$outdated} Outdated Plugin" . ( $outdated !== 1 ? 's' : '' ),
				'description'  => 'Outdated plugins can cause security vulnerabilities and conflicts. Update them as soon as possible.',
				'color'        => '#f44336',
				'bg_color'     => '#ffebee',
				'kb_link'      => 'https://wpshadow.com/kb/how-to-safely-update-plugins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-updates',
				'action_link'  => admin_url( 'plugins.php' ),
				'action_text'  => 'Update Plugins',
				'auto_fixable' => true,
				'threat_level' => 80,
			);
		}
		
		return null;
	}
	
	/**
	 * Count outdated plugins.
	 *
	 * @return int Number of plugins with available updates.
	 */
	private static function get_outdated_plugins_count() {
		$updates = get_site_transient( 'update_plugins' );
		return ! empty( $updates->response ) ? count( $updates->response ) : 0;
	}
}

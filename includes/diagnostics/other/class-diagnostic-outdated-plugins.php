<?php
declare(strict_types=1);
/**
 * Outdated Plugins Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for outdated plugins.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Outdated_Plugins extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
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
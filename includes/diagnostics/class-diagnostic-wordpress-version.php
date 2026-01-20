<?php
/**
 * WordPress Version Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Check WordPress version for updates.
 */
class Diagnostic_WordPress_Version {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wp_version;
		
		if ( version_compare( $wp_version, '6.4', '<' ) ) {
			return array(
				'id'           => 'wordpress-outdated',
				'title'        => 'WordPress Update Available',
				'description'  => "You're running WordPress {$wp_version}. Updating improves security and performance.",
				'color'        => '#2196f3',
				'bg_color'     => '#e3f2fd',
				'kb_link'      => 'https://wpshadow.com/kb/how-to-update-wordpress-safely/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=wp-update',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}

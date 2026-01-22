<?php
declare(strict_types=1);
/**
 * Plugin Count Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for excessive plugin count.
 */
class Diagnostic_Plugin_Count extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$plugins = get_plugins();
		$count   = count( $plugins );

		if ( $count > 50 ) {
			return array(
				'id'           => 'plugin-count-high',
				'title'        => "High Plugin Count ({$count})",
				'description'  => 'You have many plugins active. Consider auditing for unused ones—each adds overhead.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/audit-and-optimize-your-wordpress-plugins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-optimization',
				'auto_fixable' => false,
				'threat_level' => 40,
			);
		}

		return null;
	}
}

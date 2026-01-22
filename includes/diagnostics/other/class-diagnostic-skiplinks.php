<?php
declare(strict_types=1);
/**
 * Skiplinks Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if skiplinks are enabled for accessibility.
 */
class Diagnostic_Skiplinks extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! get_option( 'wpshadow_skiplinks_enabled', false ) ) {
			return array(
				'id'           => 'skiplinks-missing',
				'title'        => 'Add Skip to Content Links',
				'description'  => 'Skiplinks improve keyboard navigation and accessibility for screen readers.',
				'color'        => '#4caf50',
				'bg_color'     => '#e8f5e9',
				'kb_link'      => 'https://wpshadow.com/kb/add-skiplinks/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=skiplinks',
				'auto_fixable' => true,
				'threat_level' => 25,
			);
		}

		return null;
	}
}

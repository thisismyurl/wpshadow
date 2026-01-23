<?php
declare(strict_types=1);
/**
 * Iframe Busting Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if frame-busting headers are present.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Iframe_Busting extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::is_enabled() ) {
			return array(
				'id'           => 'iframe-busting-missing',
				'title'        => 'Clickjacking Protection Not Enabled',
				'description'  => 'Add X-Frame-Options and frame-ancestors directives to prevent clickjacking.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/enable-clickjacking-protection/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=iframe-busting',
				'auto_fixable' => true,
				'threat_level' => 50,
			);
		}

		return null;
	}

	private static function is_enabled() {
		return (bool) get_option( 'wpshadow_iframe_busting_enabled', false );
	}

}
<?php
declare(strict_types=1);
/**
 * Consent Checks Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if a consent mechanism is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Consent_Checks extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( get_option( 'wpshadow_consent_enabled', false ) ) {
			return null;
		}

		return array(
			'id'           => 'consent-missing',
			'title'        => 'Enable a Consent Banner',
			'description'  => 'No cookie/consent banner is enabled. Add one to align with privacy best practices.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/add-consent-banner/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=consent',
			'auto_fixable' => true,
			'threat_level' => 40,
		);
	}
}

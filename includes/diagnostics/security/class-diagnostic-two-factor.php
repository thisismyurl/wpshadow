<?php
declare(strict_types=1);
/**
 * Two-Factor Authentication Diagnostic
 *
 * Philosophy: Security hardening with education; suggests Pro/Guardian monitoring.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if two-factor authentication is encouraged or enforced for admins.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Two_Factor extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Simple heuristic: look for common 2FA plugins active
		$twofa_plugins = array(
			'two-factor/two-factor.php',
			'wp-2fa/wp-2fa.php',
			'secure-login/two-factor.php',
		);
		$active = get_option( 'active_plugins', array() );
		$has_twofa = false;
		foreach ( $twofa_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				$has_twofa = true;
				break;
			}
		}
		
		if ( $has_twofa ) {
			return null; // 2FA plugin active
		}
		
		return array(
			'title'       => 'Two-Factor Authentication Not Enabled',
			'description' => 'Admin accounts lack two-factor authentication. Enable 2FA to prevent account takeover.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-two-factor-authentication/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=twofa',
			'auto_fixable' => false,
			'threat_level' => 85,
		);
	}
}

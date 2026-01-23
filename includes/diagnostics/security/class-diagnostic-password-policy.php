<?php
declare(strict_types=1);
/**
 * Password Policy Diagnostic
 *
 * Philosophy: Security education - encourage strong passwords
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if password policy enforcement is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Password_Policy extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for password policy plugins
		$policy_plugins = array(
			'force-strong-passwords/force-strong-passwords.php',
			'better-passwords/better-passwords.php',
			'password-policy-manager/password-policy-manager.php',
			'wordfence/wordfence.php', // Has password enforcement
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $policy_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Password policy active
			}
		}
		
		return array(
			'id'          => 'password-policy',
			'title'       => 'No Password Policy Enforcement',
			'description' => 'Your site allows weak passwords. Install a password policy plugin to enforce minimum strength requirements and prevent account compromise.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enforce-password-policy/',
			'training_link' => 'https://wpshadow.com/training/password-policy/',
			'auto_fixable' => false,
			'threat_level' => 70,
		);
	}

}
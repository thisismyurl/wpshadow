<?php
declare(strict_types=1);
/**
 * Account Lockout Policy Diagnostic
 *
 * Philosophy: Brute force protection - lock out after failed attempts
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if account lockout policy is enforced.
 */
class Diagnostic_Account_Lockout_Policy extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$lockout_plugins = array(
			'wordfence/wordfence.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $lockout_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'account-lockout-policy',
			'title'       => 'No Account Lockout After Failed Attempts',
			'description' => 'No login lockout mechanism. Attackers can attempt unlimited password guesses. Implement account lockout (5+ failed attempts = 30min lockout).',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-account-lockout/',
			'training_link' => 'https://wpshadow.com/training/brute-force-protection/',
			'auto_fixable' => false,
			'threat_level' => 75,
		);
	}
}

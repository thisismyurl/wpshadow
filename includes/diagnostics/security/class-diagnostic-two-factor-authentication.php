<?php
declare(strict_types=1);
/**
 * Two-Factor Authentication (2FA) Diagnostic
 *
 * Philosophy: Authentication - multi-factor verification
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if 2FA is enabled for admin users.
 */
class Diagnostic_Two_Factor_Authentication extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$twofa_plugins = array(
			'two-factor-authentication/two-factor-authentication.php',
			'wordfence/wordfence.php',
			'google-authenticator-per-user-prompts/google-authenticator-per-user-prompts.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $twofa_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'two-factor-authentication',
			'title'       => 'No Two-Factor Authentication (2FA)',
			'description' => 'Admin login requires only password. Stolen credentials fully compromise the site. Require 2FA (authenticator app, SMS) for all admin logins.',
			'severity'    => 'critical',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-2fa/',
			'training_link' => 'https://wpshadow.com/training/two-factor-setup/',
			'auto_fixable' => false,
			'threat_level' => 85,
		);
	}
}

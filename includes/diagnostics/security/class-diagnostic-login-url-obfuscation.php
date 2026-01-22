<?php
declare(strict_types=1);
/**
 * Login URL Obfuscation Diagnostic
 *
 * Philosophy: Security through obscurity layer - reduces automated attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if default login URLs are changed.
 */
class Diagnostic_Login_URL_Obfuscation extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for login URL change plugins
		$login_plugins = array(
			'wps-hide-login/wps-hide-login.php',
			'rename-wp-login/rename-wp-login.php',
			'hide-login-page/hide-login-page.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $login_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Login URL likely changed
			}
		}
		
		return array(
			'id'          => 'login-url-obfuscation',
			'title'       => 'Default Login URL Exposed',
			'description' => 'Your site uses the default /wp-login.php and /wp-admin URLs, making it easier for automated bots to find and attack. Consider changing your login URL.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/change-wordpress-login-url/',
			'training_link' => 'https://wpshadow.com/training/login-url-security/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}

<?php declare(strict_types=1);
/**
 * Security Plugin Diagnostic
 *
 * Philosophy: Helpful neighbor - recommend centralized security tool
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if a security plugin is installed.
 */
class Diagnostic_Security_Plugin {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check for common security plugins
		$security_plugins = array(
			'wordfence/wordfence.php',
			'better-wp-security/better-wp-security.php',
			'sucuri-scanner/sucuri.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'jetpack/jetpack.php',
			'bulletproof-security/bulletproof-security.php',
			'security-ninja/security-ninja.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $security_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Security plugin active
			}
		}
		
		return array(
			'id'          => 'security-plugin',
			'title'       => 'No Security Plugin Detected',
			'description' => 'Your site lacks a dedicated security plugin for centralized monitoring, hardening, and threat detection. Consider installing Wordfence, iThemes Security, or similar.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/choose-security-plugin/',
			'training_link' => 'https://wpshadow.com/training/security-plugins/',
			'auto_fixable' => false,
			'threat_level' => 75,
		);
	}
}

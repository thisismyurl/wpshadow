<?php
declare(strict_types=1);
/**
 * IP Whitelisting Diagnostic
 *
 * Philosophy: Defense in depth - restrict admin access by IP
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if admin access is IP-restricted.
 */
class Diagnostic_IP_Whitelisting extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for IP restriction plugins
		$ip_plugins = array(
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'wordfence/wordfence.php',
			'limit-login-countries/limit-login-countries.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $ip_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // IP filtering likely configured
			}
		}
		
		// Check for .htaccess restrictions (basic check)
		$htaccess = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess ) ) {
			$content = file_get_contents( $htaccess );
			if ( strpos( $content, 'Require ip' ) !== false || strpos( $content, 'Allow from' ) !== false ) {
				return null; // IP restrictions found
			}
		}
		
		return array(
			'id'          => 'ip-whitelisting',
			'title'       => 'Admin Access Not IP-Restricted',
			'description' => 'Admin area is accessible from any IP address. Consider restricting access to known IPs via .htaccess or a security plugin for additional protection.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/restrict-admin-by-ip/',
			'training_link' => 'https://wpshadow.com/training/ip-whitelisting/',
			'auto_fixable' => false,
			'threat_level' => 65,
		);
	}
}

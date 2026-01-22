<?php declare(strict_types=1);
/**
 * IP Whitelist for Admin Dashboard Diagnostic
 *
 * Philosophy: Network security - whitelist trusted IPs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if admin dashboard IP whitelist is configured.
 */
class Diagnostic_IP_Whitelist_Admin {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$ip_whitelist = get_option( 'wpshadow_admin_ip_whitelist' );
		
		if ( empty( $ip_whitelist ) ) {
			return array(
				'id'          => 'ip-whitelist-admin',
				'title'       => 'No IP Whitelist for Admin Dashboard',
				'description' => 'Admin dashboard accepts connections from any IP. Configure IP whitelist to allow admin access only from known office IPs or VPN.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/whitelist-admin-ips/',
				'training_link' => 'https://wpshadow.com/training/ip-restrictions/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}
}

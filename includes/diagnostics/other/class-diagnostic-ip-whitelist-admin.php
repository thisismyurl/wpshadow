<?php
declare(strict_types=1);
/**
 * IP Whitelist for Admin Dashboard Diagnostic
 *
 * Philosophy: Network security - whitelist trusted IPs
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if admin dashboard IP whitelist is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_IP_Whitelist_Admin extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$ip_whitelist = get_option( 'wpshadow_admin_ip_whitelist' );

		if ( empty( $ip_whitelist ) ) {
			return array(
				'id'            => 'ip-whitelist-admin',
				'title'         => 'No IP Whitelist for Admin Dashboard',
				'description'   => 'Admin dashboard accepts connections from any IP. Configure IP whitelist to allow admin access only from known office IPs or VPN.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/whitelist-admin-ips/',
				'training_link' => 'https://wpshadow.com/training/ip-restrictions/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}
}

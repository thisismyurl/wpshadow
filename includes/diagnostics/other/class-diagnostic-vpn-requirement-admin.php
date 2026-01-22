<?php
declare(strict_types=1);
/**
 * VPN Requirement for Admin Access Diagnostic
 *
 * Philosophy: Network security - require VPN for admin
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if VPN is required for admin access.
 */
class Diagnostic_VPN_Requirement_Admin extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$vpn_required = get_option( 'wpshadow_vpn_required_admin' );
		
		if ( empty( $vpn_required ) ) {
			return array(
				'id'          => 'vpn-requirement-admin',
				'title'       => 'VPN Not Required for Admin Access',
				'description' => 'Admin dashboard accessible without VPN. Require admin users to use company VPN or specific IPs to prevent remote compromise attempts.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/require-vpn-admin/',
				'training_link' => 'https://wpshadow.com/training/vpn-access-control/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}
}

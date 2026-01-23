<?php
declare(strict_types=1);
/**
 * VPN Requirement for Admin Access Diagnostic
 *
 * Philosophy: Network security - require VPN for admin
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if VPN is required for admin access.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
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
				'id'            => 'vpn-requirement-admin',
				'title'         => 'VPN Not Required for Admin Access',
				'description'   => 'Admin dashboard accessible without VPN. Require admin users to use company VPN or specific IPs to prevent remote compromise attempts.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/require-vpn-admin/',
				'training_link' => 'https://wpshadow.com/training/vpn-access-control/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: VPN Requirement Admin
	 * Slug: -vpn-requirement-admin
	 * File: class-diagnostic-vpn-requirement-admin.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: VPN Requirement Admin
	 * Slug: -vpn-requirement-admin
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__vpn_requirement_admin(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}

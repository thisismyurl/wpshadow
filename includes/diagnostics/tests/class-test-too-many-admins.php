<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Too Many Admin Accounts
 *
 * Detects excessive administrator accounts which represent a security risk.
 * Most sites need only 1 admin, rarely more than 2.
 *
 * @since 1.2.0
 */
class Test_Too_Many_Admins extends Diagnostic_Base {


	/**
	 * Check for excessive admin accounts
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$admin_count = count( $admin_users );

		// Threshold: 3+ admin accounts is excessive
		if ( $admin_count < 3 ) {
			return null;
		}

		// Calculate threat based on count
		$threat = $admin_count >= 5 ? 80 : 60;

		// Build admin list for metadata
		$admin_list = array();
		foreach ( $admin_users as $user ) {
			$admin_list[] = array(
				'id'         => $user->ID,
				'login'      => $user->user_login,
				'email'      => $user->user_email,
				'registered' => $user->user_registered,
			);
		}

		return array(
			'threat_level'  => $threat,
			'threat_color'  => $threat >= 80 ? 'red' : 'yellow',
			'passed'        => false,
			'issue'         => sprintf(
				'Found %d administrator accounts (recommended: 1-2)',
				$admin_count
			),
			'metadata'      => array(
				'admin_count' => $admin_count,
				'admins'      => $admin_list,
			),
			'kb_link'       => 'https://wpshadow.com/kb/admin-account-security/',
			'training_link' => 'https://wpshadow.com/training/user-access-control/',
		);
	}

	/**
	 * Guardian Sub-Test: Count of admin accounts
	 *
	 * @return array Test result
	 */
	public static function test_admin_count(): array {
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$admin_count = count( $admin_users );

		return array(
			'test_name'   => 'Administrator Account Count',
			'count'       => $admin_count,
			'passed'      => $admin_count <= 2,
			'recommended' => '1-2 admin accounts',
			'description' => sprintf( 'Detected %d administrator account(s)', $admin_count ),
		);
	}

	/**
	 * Guardian Sub-Test: List of admin accounts with details
	 *
	 * @return array Test result
	 */
	public static function test_admin_list(): array {
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$admin_list  = array();

		foreach ( $admin_users as $user ) {
			$admin_list[] = array(
				'id'          => $user->ID,
				'login'       => $user->user_login,
				'email'       => $user->user_email,
				'name'        => $user->display_name,
				'registered'  => $user->user_registered,
				'last_active' => self::get_user_last_active( $user->ID ),
			);
		}

		return array(
			'test_name'   => 'Administrator Accounts List',
			'admins'      => $admin_list,
			'count'       => count( $admin_list ),
			'passed'      => count( $admin_list ) <= 2,
			'description' => sprintf(
				'Found %d administrators: %s',
				count( $admin_list ),
				implode( ', ', array_map( fn( $a ) => $a['login'], $admin_list ) )
			),
		);
	}

	/**
	 * Guardian Sub-Test: Security impact assessment
	 *
	 * @return array Test result
	 */
	public static function test_admin_security_impact(): array {
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$admin_count = count( $admin_users );

		// Assess security level
		if ( $admin_count <= 1 ) {
			$risk_level  = 'low';
			$description = 'Single admin account - consider backup access';
		} elseif ( $admin_count === 2 ) {
			$risk_level  = 'acceptable';
			$description = 'Two admin accounts - good for backup access';
		} elseif ( $admin_count < 5 ) {
			$risk_level  = 'medium';
			$description = 'Multiple admin accounts - review inactive accounts';
		} else {
			$risk_level  = 'high';
			$description = 'Too many admin accounts - significant security risk';
		}

		return array(
			'test_name'    => 'Admin Security Impact',
			'admin_count'  => $admin_count,
			'risk_level'   => $risk_level,
			'passed'       => $risk_level === 'low' || $risk_level === 'acceptable',
			'description'  => $description,
			'threat_level' => $admin_count >= 5 ? 80 : ( $admin_count >= 3 ? 60 : 0 ),
		);
	}

	/**
	 * Get when user was last active
	 *
	 * @param int $user_id User ID
	 * @return string Last active time or 'Unknown'
	 */
	private static function get_user_last_active( int $user_id ): string {
		// Try to get from user meta (if other plugins track it)
		$last_active = get_user_meta( $user_id, 'last_activity', true );

		if ( $last_active ) {
			return human_time_diff( strtotime( $last_active ) ) . ' ago';
		}

		return 'Unknown';
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Too Many Admin Accounts';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Detects excessive administrator accounts for security review';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}

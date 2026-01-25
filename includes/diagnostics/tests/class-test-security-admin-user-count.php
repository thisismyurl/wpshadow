<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Security_Admin_User_Count extends Diagnostic_Base {


	public static function check(): ?array {
		$admin_count = count_users();
		$admins      = isset( $admin_count['avail_roles']['administrator'] ) ? $admin_count['avail_roles']['administrator'] : 0;

		if ( $admins > 3 ) {
			return array(
				'id'           => 'admin-user-count',
				'title'        => 'Too Many Admin Users',
				'description'  => sprintf( 'Found %d admin users. Fewer admins reduces attack surface.', $admins ),
				'threat_level' => 40,
			);
		}
		return null;
	}

	public static function test_live_admin_user_count(): array {
		$result      = self::check();
		$admin_count = count_users();
		$admins      = isset( $admin_count['avail_roles']['administrator'] ) ? $admin_count['avail_roles']['administrator'] : 0;

		if ( $admins > 3 ) {
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Too many admins, check() should return issue.',
				);
			}
		} elseif ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Admin count OK, check() should return null.',
				);
		}

		return array(
			'passed'  => true,
			'message' => 'Admin user count check passed.',
		);
	}
}

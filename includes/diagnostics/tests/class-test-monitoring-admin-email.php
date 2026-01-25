<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Monitoring_Admin_Email extends Diagnostic_Base {

	public static function check(): ?array {
		$email = get_option( 'admin_email' );
		if ( empty( $email ) || ! is_email( $email ) ) {
			return array(
				'id'           => 'invalid-admin-email',
				'title'        => 'Admin email invalid or missing',
				'threat_level' => 50,
			);
		}
		return null;
	}

	public static function test_live_monitoring_admin_email(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Admin email valid' : 'Invalid admin email',
		);
	}
}

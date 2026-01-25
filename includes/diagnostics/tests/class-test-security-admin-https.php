<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Admin_HTTPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Security_Admin_HTTPS extends Diagnostic_Admin_HTTPS {


	public static function test_live_admin_https(): array {
		$result          = self::check();
		$has_ssl         = is_ssl();
		$force_ssl_admin = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN;

		if ( ! $has_ssl ) {
			if ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'No SSL, check() should return null but returned: ' . wp_json_encode( $result ),
				);
			}
		} elseif ( ! $force_ssl_admin ) {
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'SSL enabled but FORCE_SSL_ADMIN not set, check() should return issue.',
				);
			}
		} elseif ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'SSL and FORCE_SSL_ADMIN both enabled, check() should return null.',
				);
		}

		return array(
			'passed'  => true,
			'message' => 'Admin HTTPS check passed.',
		);
	}
}

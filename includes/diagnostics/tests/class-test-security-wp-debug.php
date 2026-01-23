<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Security_WP_DEBUG extends Diagnostic_Base {

	public static function check(): ?array {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				'id'           => 'wp-debug-enabled',
				'title'        => 'WP_DEBUG Enabled',
				'description'  => 'WP_DEBUG is enabled on production site, exposing debugging information.',
				'threat_level' => 60,
			);
		}
		return null;
	}

	public static function test_live_wp_debug(): array {
		$result = self::check();
		$debug_enabled = defined( 'WP_DEBUG' ) && WP_DEBUG;

		if ( $debug_enabled ) {
			if ( is_null( $result ) ) {
				return array(
					'passed' => false,
					'message' => 'WP_DEBUG is enabled, check() should return issue.',
				);
			}
		} else {
			if ( ! is_null( $result ) ) {
				return array(
					'passed' => false,
					'message' => 'WP_DEBUG is disabled, check() should return null.',
				);
			}
		}

		return array(
			'passed' => true,
			'message' => 'WP_DEBUG check passed.',
		);
	}
}

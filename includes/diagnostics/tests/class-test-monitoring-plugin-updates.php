<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Monitoring_Plugin_Updates_Available extends Diagnostic_Base {


	public static function check(): ?array {
		if ( ! function_exists( 'get_plugin_updates' ) ) {
			return null;
		}

		$updates = get_plugin_updates();

		if ( ! empty( $updates ) ) {
			return array(
				'id'           => 'plugin-updates-available',
				'title'        => sprintf( '%d Plugin Updates Available', count( $updates ) ),
				'description'  => 'Update plugins to get bug fixes and security patches.',
				'threat_level' => 50,
			);
		}
		return null;
	}

	public static function test_live_plugin_updates_available(): array {
		$result  = self::check();
		$updates = function_exists( 'get_plugin_updates' ) ? get_plugin_updates() : array();

		if ( ! empty( $updates ) ) {
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Plugin updates available, check() should return issue.',
				);
			}
		} elseif ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'No plugin updates, check() should return null.',
				);
		}

		return array(
			'passed'  => true,
			'message' => 'Plugin updates check passed.',
		);
	}
}

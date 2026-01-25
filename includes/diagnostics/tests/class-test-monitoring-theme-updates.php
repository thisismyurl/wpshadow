<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Monitoring_Theme_Updates_Available extends Diagnostic_Base {


	public static function check(): ?array {
		if ( ! function_exists( 'get_theme_updates' ) ) {
			return null;
		}

		$updates = get_theme_updates();

		if ( ! empty( $updates ) ) {
			return array(
				'id'           => 'theme-updates-available',
				'title'        => sprintf( '%d Theme Updates Available', count( $updates ) ),
				'description'  => 'Update themes to get bug fixes and security patches.',
				'threat_level' => 50,
			);
		}
		return null;
	}

	public static function test_live_theme_updates_available(): array {
		$result  = self::check();
		$updates = function_exists( 'get_theme_updates' ) ? get_theme_updates() : array();

		if ( ! empty( $updates ) ) {
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Theme updates available, check() should return issue.',
				);
			}
		} elseif ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'No theme updates, check() should return null.',
				);
		}

		return array(
			'passed'  => true,
			'message' => 'Theme updates check passed.',
		);
	}
}

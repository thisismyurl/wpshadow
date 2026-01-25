<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Security_DB_PREFIX_Default extends Diagnostic_Base {


	public static function check(): ?array {
		global $wpdb;

		if ( $wpdb->prefix === 'wp_' ) {
			return array(
				'id'           => 'db-prefix-default',
				'title'        => 'Default Database Prefix',
				'description'  => 'Using default "wp_" database prefix makes brute force attacks easier. Change to custom prefix.',
				'threat_level' => 50,
			);
		}
		return null;
	}

	public static function test_live_db_prefix_default(): array {
		global $wpdb;
		$result     = self::check();
		$is_default = $wpdb->prefix === 'wp_';

		if ( $is_default ) {
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Using default prefix, check() should return issue.',
				);
			}
		} elseif ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Using custom prefix, check() should return null.',
				);
		}

		return array(
			'passed'  => true,
			'message' => 'Database prefix check passed.',
		);
	}
}

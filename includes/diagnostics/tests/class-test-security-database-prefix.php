<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Security_Database_Prefix extends Diagnostic_Base {

	public static function check(): ?array {
		global $wpdb;
		if ( $wpdb->prefix === 'wp_' ) {
			return array(
				'id'           => 'default-db-prefix',
				'title'        => 'Database using default prefix "wp_"',
				'threat_level' => 40,
			);
		}
		return null;
	}

	public static function test_live_security_database_prefix(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Custom database prefix' : 'Using default prefix',
		);
	}
}

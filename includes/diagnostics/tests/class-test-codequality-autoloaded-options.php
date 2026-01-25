<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Autoloaded_Options_Size;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_CodeQuality_Autoloaded_Options_Size extends Diagnostic_Autoloaded_Options_Size {


	public static function test_live_autoloaded_options_size(): array {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return array(
				'passed'  => false,
				'message' => 'wpdb not available.',
			);
		}

		$result          = self::check();
		$threshold_bytes = 0.8 * 1024 * 1024;

		$autoloaded_size = (int) $wpdb->get_var(
			"SELECT COALESCE(SUM(CHAR_LENGTH(option_value)), 0) FROM {$wpdb->options} WHERE autoload='yes'"
		);

		if ( $autoloaded_size > $threshold_bytes ) {
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Autoloaded size exceeds threshold, check() should return issue.',
				);
			}
		} elseif ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Autoloaded size OK, check() should return null.',
				);
		}

		return array(
			'passed'  => true,
			'message' => 'Autoloaded options check passed.',
		);
	}
}

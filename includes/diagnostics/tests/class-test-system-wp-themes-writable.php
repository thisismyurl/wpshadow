<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_System_WP_Themes_Writable extends Diagnostic_Base {

	public static function check(): ?array {
		$path = get_theme_root();
		if ( ! is_writable( $path ) ) {
			return array(
				'id'           => 'themes-not-writable',
				'title'        => 'Themes directory not writable',
				'threat_level' => 60,
			);
		}
		return null;
	}

	public static function test_live_system_wp_themes_writable(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Themes writable' : 'Not writable',
		);
	}
}

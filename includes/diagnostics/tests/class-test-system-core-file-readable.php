<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_System_Core_File_Readable extends Diagnostic_Base {

	public static function check(): ?array {
		$path = ABSPATH . 'wp-load.php';
		if ( ! is_readable( $path ) ) {
			return array(
				'id'           => 'core-not-readable',
				'title'        => 'WordPress core file not readable',
				'threat_level' => 80,
			);
		}
		return null;
	}

	public static function test_live_system_core_file_readable(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Core readable' : 'Not readable',
		);
	}
}

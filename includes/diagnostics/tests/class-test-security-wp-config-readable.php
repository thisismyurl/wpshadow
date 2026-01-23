<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Security_WP_Config_Readable extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$path = ABSPATH . 'wp-config.php';
		if (!is_readable($path)) {
			return array(
				'id' => 'wp-config-not-readable',
				'title' => 'wp-config.php not readable',
				'threat_level' => 70
			);
		}
		return null;
	}

	public static function test_live_security_wp_config_readable(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'wp-config readable' : 'Not readable'
		);
	}
}

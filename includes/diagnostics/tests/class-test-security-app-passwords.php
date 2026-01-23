<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Security_App_Passwords extends Diagnostic_Base
{
	public static function check(): ?array
	{
		if (!function_exists('wp_get_application_passwords')) {
			return array(
				'id' => 'app-passwords-unavailable',
				'title' => 'Application passwords not available',
				'threat_level' => 30
			);
		}
		return null;
	}

	public static function test_live_security_app_passwords(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'Application passwords available' : 'Not available'
		);
	}
}

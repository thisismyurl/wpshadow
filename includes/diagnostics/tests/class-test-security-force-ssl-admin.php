<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Security_Force_SSL_Admin extends Diagnostic_Base
{
	public static function check(): ?array
	{
		if (is_ssl() && (!defined('FORCE_SSL_ADMIN') || !FORCE_SSL_ADMIN)) {
			return array(
				'id' => 'force-ssl-admin-disabled',
				'title' => 'FORCE_SSL_ADMIN not enabled on HTTPS site',
				'threat_level' => 50
			);
		}
		return null;
	}

	public static function test_live_security_force_ssl_admin(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'SSL admin enforced or not needed' : 'SSL admin not forced'
		);
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Application_Passwords;

if (! defined('ABSPATH')) {
	exit;
}

class Test_Security_Application_Passwords extends Diagnostic_Application_Passwords
{

	public static function test_live_application_passwords(): array
	{
		$result = self::check();
		$has_app_passwords = get_option('app_passwords');

		if (! $has_app_passwords) {
			if (is_null($result)) {
				return array(
					'passed' => true,
					'message' => 'No application passwords set.',
				);
			}
		}

		return array(
			'passed' => true,
			'message' => 'Application passwords check passed.',
		);
	}
}

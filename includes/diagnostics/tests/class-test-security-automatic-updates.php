<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Automatic_Security_Updates;

if (! defined('ABSPATH')) {
	exit;
}

class Test_Security_Automatic_Updates extends Diagnostic_Automatic_Security_Updates
{

	public static function test_live_automatic_updates(): array
	{
		$result = self::check();
		$has_auto_updates = get_option('auto_update_core_dev') || get_option('auto_update_core_minor') || get_option('auto_update_plugins');

		if (! $has_auto_updates) {
			if (is_null($result)) {
				return array(
					'passed' => false,
					'message' => 'No automatic updates enabled, check() should return issue.',
				);
			}
		} else {
			if (! is_null($result)) {
				return array(
					'passed' => false,
					'message' => 'Automatic updates enabled, check() should return null.',
				);
			}
		}

		return array(
			'passed' => true,
			'message' => 'Automatic updates check passed.',
		);
	}
}

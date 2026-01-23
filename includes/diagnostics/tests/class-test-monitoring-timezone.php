<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Monitoring_Timezone extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$tz = get_option('timezone_string');
		if (empty($tz)) {
			return array(
				'id' => 'timezone-not-set',
				'title' => 'Timezone not configured',
				'threat_level' => 30
			);
		}
		return null;
	}

	public static function test_live_monitoring_timezone(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'Timezone set' : 'Timezone not configured'
		);
	}
}

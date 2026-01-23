<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Security_WP_Debug_Log extends Diagnostic_Base
{
	public static function check(): ?array
	{
		if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
			return array(
				'id' => 'wp-debug-log-disabled',
				'title' => 'WP_DEBUG_LOG not enabled',
				'threat_level' => 40
			);
		}
		return null;
	}

	public static function test_live_security_wp_debug_log(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'WP_DEBUG_LOG enabled' : 'WP_DEBUG_LOG not configured'
		);
	}
}

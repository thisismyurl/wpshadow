<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_System_WP_Content_Writable extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$path = ABSPATH . 'wp-content';
		if (!is_writable($path)) {
			return array(
				'id' => 'wp-content-not-writable',
				'title' => 'wp-content directory not writable',
				'threat_level' => 60
			);
		}
		return null;
	}

	public static function test_live_system_wp_content_writable(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'wp-content writable' : 'Not writable'
		);
	}
}

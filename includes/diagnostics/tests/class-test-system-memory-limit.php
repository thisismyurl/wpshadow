<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_System_Memory_Limit extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$limit = WP_MEMORY_LIMIT;
		if (is_numeric($limit)) {
			$limit_bytes = $limit;
		} else {
			$limit_bytes = wp_convert_hr_to_bytes($limit);
		}

		if ($limit_bytes < (64 * 1024 * 1024)) {
			return array(
				'id' => 'memory-limit-low',
				'title' => 'Memory limit below 64MB: ' . size_format($limit_bytes),
				'threat_level' => 50
			);
		}
		return null;
	}

	public static function test_live_system_memory_limit(): array
	{
		$result = self::check();
		return array(
			'passed' => $result === null,
			'message' => $result === null ? 'Memory limit adequate' : 'Low memory limit'
		);
	}
}

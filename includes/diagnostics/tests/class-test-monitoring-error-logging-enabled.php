<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Error Logging Enabled (Monitoring)
 *
 * Checks if WordPress error logging is properly configured
 * Philosophy: Show value (#9) - error logging helps diagnosis
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_ErrorLoggingEnabled extends Diagnostic_Base
{

	public static function check(): ?array
	{
		// Check if error logging is enabled
		if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
			return [
				'id' => 'error-logging-enabled',
				'title' => __('Error logging not enabled', 'wpshadow'),
				'description' => __('Enable error logging (WP_DEBUG_LOG) in wp-config.php to diagnose issues. Keep WP_DEBUG off on production.', 'wpshadow'),
				'severity' => 'low',
				'threat_level' => 20,
			];
		}

		return null;
	}

	public static function test_live_error_logging_enabled(): array
	{
		$result = self::check();

		if (null === $result) {
			return [
				'passed' => true,
				'message' => __('Error logging is properly enabled', 'wpshadow'),
			];
		}

		return [
			'passed' => false,
			'message' => $result['description'],
		];
	}
}

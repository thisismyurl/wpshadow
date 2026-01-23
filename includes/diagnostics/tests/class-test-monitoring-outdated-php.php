<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Outdated PHP Version (Monitoring)
 *
 * Checks if PHP version is outdated and no longer receiving security updates
 * Philosophy: Show value (#9) - modern PHP prevents vulnerabilities
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_OutdatedPhp extends Diagnostic_Base
{

	public static function check(): ?array
	{
		$php_version = phpversion();

		// Check PHP versions that are no longer supported
		// PHP 7.2 EOL: 2020-11-30
		// PHP 7.3 EOL: 2021-12-06
		// PHP 7.4 EOL: 2022-11-28
		if (version_compare($php_version, '8.0.0', '<')) {
			return [
				'id' => 'outdated-php',
				'title' => sprintf(__('PHP %s is no longer receiving security updates', 'wpshadow'), $php_version),
				'description' => __('Upgrade to PHP 8.0+ to receive security patches and performance improvements.', 'wpshadow'),
				'severity' => 'high',
				'threat_level' => 70,
			];
		}

		return null;
	}

	public static function test_live_outdated_php(): array
	{
		$result = self::check();

		if (null === $result) {
			return [
				'passed' => true,
				'message' => sprintf(__('PHP %s is current and supported', 'wpshadow'), phpversion()),
			];
		}

		return [
			'passed' => false,
			'message' => $result['description'],
		];
	}
}

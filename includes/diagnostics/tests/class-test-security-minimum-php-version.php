<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Minimum PHP Version (Security)
 *
 * Checks that WordPress is running on a supported PHP version (7.4+)
 * Philosophy: Show value (#9) - secure systems run safer, faster
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_MinimumPhpVersion extends Diagnostic_Base
{

	public static function check(): ?array
	{
		$php_version = phpversion();
		$min_version = '7.4.0';

		if (version_compare($php_version, $min_version, '<')) {
			return [
				'id' => 'minimum-php-version',
				'title' => sprintf(__('PHP %s is outdated', 'wpshadow'), $php_version),
				'description' => sprintf(
					__('WordPress requires PHP %s or higher for security patches. Current: %s', 'wpshadow'),
					$min_version,
					$php_version
				),
				'severity' => 'critical',
				'threat_level' => 90,
			];
		}

		return null;
	}

	public static function test_live_minimum_php_version(): array
	{
		$result = self::check();

		if (null === $result) {
			return [
				'passed' => true,
				'message' => sprintf(__('PHP version %s is secure', 'wpshadow'), phpversion()),
			];
		}

		return [
			'passed' => false,
			'message' => $result['description'],
		];
	}
}

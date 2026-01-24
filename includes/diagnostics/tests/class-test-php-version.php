<?php

/**
 * WPShadow System Diagnostic Test: PHP Version Compatibility
 *
 * Tests if PHP version meets minimum requirements and is not approaching EOL.
 *
 * Testable via: phpversion()
 * Can be requested by Guardian: "test-php-version-compatibility", "test-php-version-eol"
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Proactive warning before PHP EOL
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: PHP Version Compatibility
 *
 * Main diagnostic that checks PHP version requirements.
 * Can request specific sub-tests via Guardian.
 *
 * @verified Not yet tested
 */
class Test_PHP_Version extends Diagnostic_Base
{

	protected static $slug = 'php-version';
	protected static $title = 'PHP Version Compatibility';
	protected static $description = 'Checks PHP version for compatibility and EOL status.';

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		$php_version = phpversion();

		// Check for outdated PHP versions
		if (version_compare($php_version, '7.4', '<')) {
			return array(
				'id'            => static::$slug . '-critical',
				'title'         => "Critical: PHP {$php_version} is unsupported",
				'description'   => 'WordPress 6.3+ requires PHP 7.4+. Your site may experience errors or security issues.'
				'kb_link'       => 'https://wpshadow.com/kb/php-version-compatible/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-version',
				'training_link' => 'https://wpshadow.com/training/php-version-compatible/',
				'auto_fixable'  => false,
				'threat_level'  => 90,
				'module'        => 'System',
				'priority'      => 1,
				'meta'          => array(
					'current_version' => $php_version,
					'minimum_required' => '7.4',
					'recommended' => '8.2+',
				),
			);
		}

		// Check for EOL PHP versions (7.4 EOL Nov 2022, 8.0 EOL Nov 2023, 8.1 EOL Nov 2024)
		if (version_compare($php_version, '8.0', '<')) {
			return array(
				'id'            => static::$slug . '-eol-warning',
				'title'         => "Warning: PHP {$php_version} is nearing end-of-life",
				'description'   => 'PHP 7.4 reached EOL November 28, 2022. Plan upgrade to PHP 8.2+ to avoid security vulnerabilities.'
				'kb_link'       => 'https://wpshadow.com/kb/php-version-compatible/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-version',
				'training_link' => 'https://wpshadow.com/training/php-version-compatible/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
				'module'        => 'System',
				'priority'      => 2,
				'meta'          => array(
					'current_version' => $php_version,
					'eol_date' => '2022-11-28',
					'recommended' => '8.2+',
				),
			);
		}

		if (version_compare($php_version, '8.2', '<')) {
			return array(
				'id'            => static::$slug . '-outdated',
				'title'         => "PHP {$php_version} is outdated",
				'description'   => 'PHP 8.2+ provides significant performance improvements and security patches. Consider upgrading.'
				'kb_link'       => 'https://wpshadow.com/kb/php-version-compatible/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-version',
				'training_link' => 'https://wpshadow.com/training/php-version-compatible/',
				'auto_fixable'  => false,
				'threat_level'  => 40,
				'module'        => 'System',
				'priority'      => 3,
				'meta'          => array(
					'current_version' => $php_version,
					'latest_stable' => '8.3',
					'performance_gain' => '10-20% faster',
				),
			);
		}

		// All good - PHP version is current and secure
		return null;
	}

	/**
	 * Guardian can request: "test-php-version-minimum-requirement"
	 * Specifically checks: PHP >= 7.4
	 */
	public static function test_php_version_minimum_requirement(): array
	{
		$php_version = phpversion();
		$passed = version_compare($php_version, '7.4', '>=');

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ PHP {$php_version} meets minimum requirement (7.4+)"
				: "✗ PHP {$php_version} is below minimum requirement (7.4+)",
			'data'    => array(
				'current_version' => $php_version,
				'minimum' => '7.4',
			),
		);
	}

	/**
	 * Guardian can request: "test-php-version-eol-status"
	 * Specifically checks: PHP version EOL dates
	 */
	public static function test_php_version_eol_status(): array
	{
		$php_version = phpversion();

		$eol_dates = array(
			'7.4' => '2022-11-28',
			'8.0' => '2023-11-23',
			'8.1' => '2024-11-25',
			'8.2' => '2025-12-08',
		);

		$version_prefix = substr($php_version, 0, 3);
		$is_eol = isset($eol_dates[$version_prefix]) && strtotime($eol_dates[$version_prefix]) < time();

		return array(
			'passed'  => ! $is_eol,
			'message' => $is_eol
				? "✗ PHP {$php_version} is past EOL date"
				: "✓ PHP {$php_version} is still supported",
			'data'    => array(
				'current_version' => $php_version,
				'eol_date' => $eol_dates[$version_prefix] ?? 'Unknown',
				'is_eol' => $is_eol,
			),
		);
	}

	/**
	 * Guardian can request: "test-php-version-recommended"
	 * Specifically checks: PHP >= 8.2
	 */
	public static function test_php_version_recommended(): array
	{
		$php_version = phpversion();
		$passed = version_compare($php_version, '8.2', '>=');

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ PHP {$php_version} meets recommended version (8.2+)"
				: "⚠ PHP {$php_version} is below recommended (8.2+)",
			'data'    => array(
				'current_version' => $php_version,
				'recommended' => '8.2+',
				'performance_gain_vs_74' => '15-25%',
			),
		);
	}
}

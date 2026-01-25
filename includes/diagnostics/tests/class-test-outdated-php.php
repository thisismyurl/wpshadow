<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Outdated PHP Version
 *
 * Detects when PHP version is below recommended minimum (7.4+).
 * Older PHP versions have security vulnerabilities and performance issues.
 *
 * @since 1.2.0
 */
class Test_Outdated_PHP extends Diagnostic_Base {


	private const RECOMMENDED_PHP    = '7.4.0';
	private const MINIMUM_SECURE_PHP = '8.0.0';

	/**
	 * Check for outdated PHP version
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$current_version = phpversion();

		// Parse versions for comparison
		if ( version_compare( $current_version, self::RECOMMENDED_PHP, '>=' ) ) {
			return null; // PHP version is good
		}

		$threat = version_compare( $current_version, self::MINIMUM_SECURE_PHP, '<' ) ? 90 : 60;

		return array(
			'threat_level'  => $threat,
			'threat_color'  => $threat >= 90 ? 'red' : 'orange',
			'passed'        => false,
			'issue'         => sprintf(
				'PHP %s is outdated (recommended: %s+)',
				$current_version,
				self::RECOMMENDED_PHP
			),
			'metadata'      => array(
				'current_version'     => $current_version,
				'recommended_version' => self::RECOMMENDED_PHP,
				'minimum_secure'      => self::MINIMUM_SECURE_PHP,
				'php_info'            => array(
					'sapi_type'    => php_sapi_name(),
					'os'           => PHP_OS,
					'memory_limit' => ini_get( 'memory_limit' ),
				),
			),
			'kb_link'       => 'https://wpshadow.com/kb/php-version-upgrade/',
			'training_link' => 'https://wpshadow.com/training/php-security/',
		);
	}

	/**
	 * Guardian Sub-Test: Current PHP version
	 *
	 * @return array Test result
	 */
	public static function test_php_version(): array {
		$current     = phpversion();
		$recommended = self::RECOMMENDED_PHP;
		$is_good     = version_compare( $current, $recommended, '>=' );

		return array(
			'test_name'       => 'PHP Version',
			'current_version' => $current,
			'recommended'     => $recommended,
			'passed'          => $is_good,
			'description'     => sprintf( 'Running PHP %s%s', $current, $is_good ? ' ✓' : ' (outdated)' ),
		);
	}

	/**
	 * Guardian Sub-Test: Security assessment
	 *
	 * @return array Test result
	 */
	public static function test_php_security(): array {
		$current   = phpversion();
		$is_secure = version_compare( $current, self::MINIMUM_SECURE_PHP, '>=' );

		$risk_level = version_compare( $current, '7.0', '<' ) ? 'critical' : ( version_compare( $current, '7.2', '<' ) ? 'high' : ( version_compare( $current, self::MINIMUM_SECURE_PHP, '<' ) ? 'medium' : 'low' ) );

		return array(
			'test_name'       => 'PHP Security Level',
			'current_version' => $current,
			'risk_level'      => $risk_level,
			'passed'          => $is_secure,
			'description'     => sprintf(
				'Security: %s - %s',
				strtoupper( $risk_level ),
				$is_secure ? 'Actively maintained' : 'No longer supported'
			),
		);
	}

	/**
	 * Guardian Sub-Test: Performance impact
	 *
	 * @return array Test result
	 */
	public static function test_php_performance(): array {
		$current = phpversion();

		$performance_gains = version_compare( $current, '7.0', '<' ) ? '300-400%' : ( version_compare( $current, '7.4', '<' ) ? '20-40%' : 'Latest' );

		return array(
			'test_name'             => 'PHP Performance Impact',
			'current_version'       => $current,
			'potential_improvement' => $performance_gains,
			'description'           => sprintf( 'Upgrading could improve performance by %s', $performance_gains ),
		);
	}

	/**
	 * Guardian Sub-Test: Server environment info
	 *
	 * @return array Test result
	 */
	public static function test_php_environment(): array {
		return array(
			'test_name'        => 'PHP Environment',
			'version'          => phpversion(),
			'sapi_type'        => php_sapi_name(),
			'operating_system' => PHP_OS,
			'memory_limit'     => ini_get( 'memory_limit' ),
			'max_execution'    => ini_get( 'max_execution_time' ),
			'upload_max'       => ini_get( 'upload_max_filesize' ),
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Outdated PHP Version';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Checks if PHP version meets security and performance standards';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}

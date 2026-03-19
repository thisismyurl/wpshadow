<?php
/**
 * Base Test Case for WPShadow Tests
 *
 * Provides common functionality for all test cases.
 *
 * @package WPShadow\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests;

use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

/**
 * Base test case class.
 */
abstract class TestCase extends PHPUnit_TestCase {

	/**
	 * Setup before each test
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// Reset any static caches
		$this->resetCaches();
	}

	/**
	 * Cleanup after each test
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		parent::tearDown();

		// Reset any static caches
		$this->resetCaches();
	}

	/**
	 * Reset all static caches
	 *
	 * @return void
	 */
	protected function resetCaches(): void {
		// Override in child classes if needed
	}

	/**
	 * Assert that a finding array has required keys
	 *
	 * @param array $finding Finding array to validate.
	 * @return void
	 */
	protected function assertValidFinding( array $finding ): void {
		$required_keys = array(
			'id',
			'title',
			'description',
			'severity',
			'threat_level',
			'auto_fixable',
		);

		foreach ( $required_keys as $key ) {
			$this->assertArrayHasKey(
				$key,
				$finding,
				"Finding must have key: {$key}"
			);
		}

		// Validate threat_level range
		$this->assertIsInt(
			$finding['threat_level'],
			'Threat level must be an integer'
		);
		$this->assertGreaterThanOrEqual(
			0,
			$finding['threat_level'],
			'Threat level must be >= 0'
		);
		$this->assertLessThanOrEqual(
			100,
			$finding['threat_level'],
			'Threat level must be <= 100'
		);

		// Validate severity
		$valid_severities = array( 'low', 'medium', 'high', 'critical' );
		$this->assertContains(
			$finding['severity'],
			$valid_severities,
			'Severity must be one of: ' . implode( ', ', $valid_severities )
		);

		// Validate auto_fixable
		$this->assertIsBool(
			$finding['auto_fixable'],
			'auto_fixable must be boolean'
		);
	}

	/**
	 * Assert that a treatment result has required keys
	 *
	 * @param array $result Treatment result to validate.
	 * @return void
	 */
	protected function assertValidTreatmentResult( array $result ): void {
		$required_keys = array( 'success', 'message' );

		foreach ( $required_keys as $key ) {
			$this->assertArrayHasKey(
				$key,
				$result,
				"Treatment result must have key: {$key}"
			);
		}

		$this->assertIsBool(
			$result['success'],
			'Treatment result success must be boolean'
		);
		$this->assertIsString(
			$result['message'],
			'Treatment result message must be string'
		);
	}

	/**
	 * Mock WordPress function if not exists
	 *
	 * @param string $function Function name.
	 * @param mixed  $return   Return value.
	 * @return void
	 */
	protected function mockWPFunction( string $function, $return ): void {
		if ( ! function_exists( $function ) ) {
			eval( "function {$function}() { return " . var_export( $return, true ) . '; }' );
		}
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	protected function getPluginVersion(): string {
		if ( defined( 'WPSHADOW_VERSION' ) ) {
			return WPSHADOW_VERSION;
		}
		return '1.6030.2148';
	}

	/**
	 * Get plugin path
	 *
	 * @return string
	 */
	protected function getPluginPath(): string {
		if ( defined( 'WPSHADOW_PLUGIN_DIR' ) ) {
			return WPSHADOW_PLUGIN_DIR;
		}
		return dirname( __DIR__, 2 );
	}
}
